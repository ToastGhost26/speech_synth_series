<?php

// amount of delay is N * speedfactor / 16 milliseconds.
//  where N is:
//             524 for Pondering  (alt-F is one of these, does nothing in editor) =  !   (0,33)
//                                (alt-E is another                             ) =     (0,18)
//              70 for Special    (alt-C is one of these, does nothing in editor) =  .   (0,46)
//              25 for FastCtrl   (alt-B is one of these, does nothing in editor) =  0   (0,48)
//          10..44 for CtrlK
//   1000/(10..60) for SlowKey  i.e. 16..100
//                 for AlNum, ( rand(modifier*6) + 20 + rand(46) + rand(93) ) / 3
//                             modifier is 1..18
//                           i.e. 6..88
//   1000/(8..73)  for Arrow i.e. 13..125
//
//  If the same key is pressed twice in a row,
//  the first repeat will be N=70 and and further hits will be N=15 each,
//  regardless of key, UNLESS a speed-change prefix (��) is given first.
// 
define('DELAY1ms', "��\1\0000"); // 1 ms
define('DELAY5ms', "��\1\0000\0."); // 5 ms
define('DELAY10ms', "��\a\0000"); // 10 ms
define('DELAY20ms', "��\r\0000"); // 20 ms
define('DELAY30ms', "��\a\0."); // 30 ms
define('DELAY40ms', "��\32\0000"); // 40 ms
define('DELAY50ms', "�� \0000"); // 50 ms
define('DELAY100ms', "��\27\0."); // 100 ms
define('DELAY150ms', "��`\0000"); // 150 ms
define('DELAY200ms', "��\200\0000"); // 200 ms
define('DELAY250ms', "��\240\0000"); // 250 ms (not a typo!)
define('DELAY280ms', "��@\0."); // 280 ms
define('DELAY300ms', "��\300\0000"); // 300 ms
define('DELAY350ms', "��P\0."); // 350 ms
define('DELAY400ms', "��\0\0000"); // 400 ms
define('DELAY500ms', "��\1\0!��k\0."); // 500 ms
define('DELAY750ms', "��%\0.��\22\0!"); // 750 ms
define('DELAY1s', "��a\0.\0000\0."); // 1000 ms
define('DELAY1p5s', "��Q\0.��#\0!"); // 1500 ms
define('DELAY2s', "��&\0.��8\0!"); // 2000 ms
define('DELAY2p5s', "��(\0.��G\0!"); // 2500 ms
define('DELAY3s', "��9\0.��T\0!"); // 3000 ms
define('DELAY4s', "��.\0.��t\0!"); // 4000 ms
define('DELAY5s', "��#\0.��\224\0!"); // 5000 ms
define('DELAY6s', "��6\0.��\260\0!"); // 6000 ms
define('DELAY7s', "��+\0.��\320\0!"); // 7000 ms
define('DELAY8s', "�� \0.��\360\0!"); // 8000 ms
define('DELAY9s', "��\215\0.��\0\0!"); // 9000 ms
define('DELAY10s', "��<\0!��s\0!\0.\0!"); // 10000 ms
define('DELAY11s', "��A\0!��\357\0.\0!"); // 11000 ms
define('DELAY12s', "��h\0!��{\0!\0.\0!"); // 12000 ms
define('DELAY13s', "��u\0!��\367\0.\0!"); // 13000 ms
define('DELAY14s', "��8\0.��\322\0!\0\22"); // 14000 ms
define('DELAY15s', "��\251\0!���\0.\0!"); // 15000 ms
define('DELAY20s', "��P\0.��\310\0!\0\22\0!"); // 20000 ms
define('DELAY25s', "��\346\0!��\372\0!\0.\0!"); // 25000 ms
define('DELAY30s', "��u\0!���\0.\0!\0\22\0!"); // 30000 ms
define('DELAY40s', "��\274\0!��\372\0!\0.\0!\0\22\0!"); // 40000 ms
define('DELAY50s', "��*\0.��\370\0!\0.\0!\0\22\0!\0\22\0!"); // 50000 ms
define('DELAY60s', "��,\0.��\0\0.\0!\0\22\0!\0\22\0!\0\22\0!"); // 60000 ms
define('DELAY100s', "��\351\0!\0.\0!\0.\0!\0\22\0!��\0\0.\0!\0\22\0!\0\22\0!\0\22\0!"); // 100000 ms

define('F1', ' ;');
define('F2', ' <');
define('F3', ' =');
define('F4', ' >');
define('F5', ' ?');
define('F6', ' @');
define('F7', ' A');
define('F8', ' B');
define('F9', ' C');
define('F10',' D');
define('CT', '');
define('LT', ' K');
define('RT', ' M');
define('UP', ' H');
define('DN', ' P');
define('INS', ' R');

define('SF1', ' T');
define('SF2', ' U');
define('SF3', ' V');
define('SF4', ' W');
define('SF5', ' X');
define('SF6', ' Y');
define('SF7', ' Z');

define('COMPRESS_DELETE_NEWLINES', true);

if($argc < 2)
{
  print "Usage: php new.php definitionsfile\n";
  return;
}

/* Import settings */
$screens     = Array();
$line_anchor = Array();
$selections  = Array();
$bigdelay    = Array();

$opt = getopt('p::f::', Array('plan::','show_plan::','final_output::','final::'));
foreach(Array('p'=>['plan','show_plan'], 'f'=>['final','final_output']) as $letter=>$opts)
  foreach($opts as $v)
    if(isset($opt[$v]) && empty($opt[$letter]))
      $opt[$letter] = $opt[$v];

$argv = array_values(array_filter($argv, function($s){return !preg_match('/^-/', $s);}));
require $argv[1];


$MAX_LEVEL    = MAX_LEVEL;
$SHOW_PLAN    = isset($opt['p']);
$FINAL_OUTPUT = isset($opt['f']);
if($FINAL_OUTPUT && is_string($opt['f'])) $MAX_LEVEL = (int)$opt['f'];
$FIRST_PLAN = 0;
if(defined('FIRST_PLAN')) $FIRST_PLAN = FIRST_PLAN;
if($SHOW_PLAN && is_string($opt['p'])) $FIRST_PLAN = (int)$opt['p'];


/********************/
$updown     = 0;
$insertmode_wanted  = true;
$insertmode_current = true;
$current_paste = '';
function FlushThisUpdown(&$input, &$updown)
{
  if($updown < 0) $input .= str_repeat(UP, -$updown); // up
  if($updown > 0) $input .= str_repeat(DN,  $updown); // down
  $updown = 0;
}
function FlushUpdown(&$input)
{
  global $updown;
  FlushThisUpdown($input, $updown);
}
function FlushInsertMode(&$input)
{
  global $insertmode_wanted, $insertmode_current;
  if($insertmode_wanted != $insertmode_current)
  {
    $input .= INS;
    $insertmode_current = $insertmode_wanted;
  }
}
function InvalidateBlock()
{
  global $current_paste;
  $current_paste = '';
}

/********************/

function FindBeginningSpaceCount($line, $pos = 0)
{
  return strspn($line, ' ', $pos);
}

function InsertLine(&$result, $position, $line)
{
  InsertLines($result, $position, Array($line));
}
function InsertLines(&$result, $position, $lines)
{
  $old_result = $result;
  $n = count($lines);
  foreach($old_result as $y => $t)
    if($y >= $position)
      $result[$y + $n] = $t;
  $n=0;
  foreach($lines as $line)
    $result[$position + $n++] = $line;
}
function EraseLines(&$result, $position, $num_lines)
{
  $n=0;
  for($y=$position; ; ++$y)
  {
    if(isset($result[$y+$num_lines]))
      $result[$y] = $result[$y+$num_lines];
    else
    {
      unset($result[$y]);
      if(++$n >= $num_lines) break;
    }
  }
}

function OptimizeDiff($diff)
{
  // DIFF POSTPROCESSING STEP 1: CONVERT STRIPES OF MATERIAL INTO REPETITIONS
  $edited_diff = Array();
  $prev  = null;
  $from  = 0;
  $to    = 0;
  $flush = function()use(&$prev,&$from,&$to,&$edited_diff)
  {
    if($from == $to && $to > 0)
      $edited_diff[] = ($to > 1 ? " {$prev}x{$to}" : " {$prev}");
    else
    {
      if($from > 0)
        $edited_diff[] = ($from > 1 ? "-{$prev}x{$from}" : "-{$prev}");
      if($to > 0)
        $edited_diff[] = ($to > 1 ? "+{$prev}x{$to}" : "+{$prev}");
    }
  };
  foreach($diff as $line)
    switch($line[0])
    {
      case '@': default: break;
      case '-': $sub=1; $add=0; goto common;
      case '+': $sub=0; $add=1; goto common;
      case ' ': $sub=1; $add=1; goto common;
      common:
        $s = substr($line, 1);
        if(isset($prev))
        {
          if($prev == $s && ($s < 65 || $s > 126))
               { $from += $sub; $to += $add; }
          else { $flush(); goto setnew; }
        }
        else { setnew: $prev=$s; $from=$sub; $to=$add; }
        break;
    }
  $flush();
  $diff = $edited_diff;

  // DIFF POSTPROCESSING STEP 2: INTERNALIZE SHORT SKIPS OVER SAME CONTENT
  #print "Through:\n"; print_r($diff);
  $edited_diff = Array();
  $same = Array();
  foreach($diff as $line)
  {
    switch($line[0])
    {
      case '@': default: break;
      case '-':
        if(count($same) <= 2 && count($edited_diff) > 0)
        {
          foreach($same as $s) { $edited_diff[] = "-$s"; $edited_diff[] = "+$s"; }
          $same = Array();
        }
        foreach($same as $s) $edited_diff[] = " $s"; $same = Array();
        $edited_diff[] = $line;
        break;
      case '+':
        if(count($same) <= 2 && count($edited_diff) > 0)
        {
          foreach($same as $s) { $edited_diff[] = "-$s"; $edited_diff[] = "+$s"; }
          $same = Array();
        }
        foreach($same as $s) $edited_diff[] = " $s"; $same = Array();
        $edited_diff[] = $line;
        break;
      case ' ':
        $same[] = substr($line, 1);
        break;
    }
  }
  $diff = $edited_diff;
  #print "Into:\n"; print_r($diff);

  // DIFF POSTPROCESSING STEP 3: CONVERT REPETITIONS BACK INTO CHARS
  $edited_diff = Array();
  $nlines = count($diff);
  for($lineno = 0; $lineno < $nlines; ++$lineno)
  {
    $line = $diff[$lineno];
    $s = substr($line, 1);
    if($line[0] == '-' && ($lineno+1) < $nlines && $diff[$lineno+1][0] == '+')
    {
      // Deal with a replace
      $s2 = substr($diff[$lineno+1], 1);
      $diff[$lineno + 1] = '@';
      // Find whether the next one or previous one is an edit
      $previous_is_an_edit = !empty($edited_diff) && $edited_diff[count($edited_diff)-1][0] != ' ';
      $next_is_an_edit     = ($lineno+2) < $nlines && $diff[$lineno+2][0] != ' ';
      // Find counts
      $first_count  = 1;
      $second_count = 1;
      if(preg_match('/^(.*)x(.*)$/', $s,  $mat)) { $first_count  = (int)$mat[2]; $s  = $mat[1]; }
      if(preg_match('/^(.*)x(.*)$/', $s2, $mat)) { $second_count = (int)$mat[2]; $s2 = $mat[1]; }
      //
      if($s == $s2)
      {
        #print "Change $first_count to $second_count\n"; flush();
        // Same item, but change in counts
        $difference = $second_count - $first_count;
        $preserve   = min($first_count, $second_count);
        if($first_count == $second_count)
        {
          // If the diff contained -x +x, don't convert it into a skip
          while($first_count--)  { $edited_diff[] = '-' . $s; }
          while($second_count--) { $edited_diff[] = '+' . $s2; }
        }
        elseif($previous_is_an_edit || !$next_is_an_edit)
        {
          // Put the edit in the beginning
          if($difference > 0)
            while($difference--) { $edited_diff[] = '+' . $s; }
          else
            while($difference++) { $edited_diff[] = '-' . $s; }
          while($preserve--) { $edited_diff[] = ' ' . $s; }
        }
        else
        {
          // Put the edit in the ending
          while($preserve--) { $edited_diff[] = ' ' . $s; }
          if($difference > 0)
            while($difference--) { $edited_diff[] = '+' . $s; }
          else
            while($difference++) { $edited_diff[] = '-' . $s; }
        }
      }
      else
      {
        #print "Change {$first_count} of {$s} into {$second_count} of {$s2}\n";
        // Different item.
        while($first_count--)  { $edited_diff[] = '-' . $s; }
        while($second_count--) { $edited_diff[] = '+' . $s2; }
      }
    }
    else
    {
      if(preg_match('/^(.*)x(.*)$/', $s, $mat))
      {
        #print "Parsed {$s}: n={$mat[2]} s={$mat[1]}\n";
        for($n=(int)$mat[2]; $n-- > 0; )
          $edited_diff[] = $line[0] . $mat[1];
      }
      else
        $edited_diff[] = $line;
    }
  }
  
  $diff = $edited_diff;
  return $diff;
}

function CreateDiffReceipt($curline, $newline)
{
  $cur_length = strlen($curline);
  $new_length = strlen($newline);
  $cur = '';
  for($a=0; $a<$cur_length; ++$a) $cur .= sprintf("%d\n", ord($curline[$a]));
  $new = '';
  for($a=0; $a<$new_length; ++$a) $new .= sprintf("%d\n", ord($newline[$a]));
  //
  $tmp1 = tempnam('/tmp', 'diff'); file_put_contents($tmp1, $cur);
  $tmp2 = tempnam('/tmp', 'diff'); file_put_contents($tmp2, $new);
  exec("diff -dU9999 $tmp1 $tmp2|tail -n +3", $diff);
  unlink($tmp1);
  unlink($tmp2);

  #print "For $curline\nTo  $newline\n"; print_r($diff);
  $diff = OptimizeDiff($diff);
  #print "Edited to:\n"; print_r($diff);

  return $diff;
}

function EnterLine($newline, $curline, &$curx,
                   $lines_above,
                   &$copypaste_hints, $copypaste_curlineno)
{
  global $updown, $insertmode_wanted;


  /* Do a character-by-character diff between the old line and the new line */
  $diff = CreateDiffReceipt($curline, $newline);

  $input  = '';
  $beginx = $curx;

  $del  = '';
  $add  = '';
  $same = '';

  $seq = Array();

  $diff[] = ' 0'; // just for flushing
  foreach($diff as $line)
    switch($line[0])
    {
      case '@': default: break;
      case '-':
        if(strlen($same)) { $seq[] = $same; $same = ''; }
        $del .= chr( (int) substr($line, 1) );
        break;
      case '+':
        if(strlen($same)) { $seq[] = $same; $same = ''; }
        $add .= chr( (int) substr($line, 1) );
        break;
      case ' ':
        if($del || strlen($add))
        {
          $seq[] = Array($del, $add);
          $del = '';
          $add = '';
        }
        $same .= chr( (int) substr($line, 1) );
        break;
    }
  // DIFF POSTPROCESSING STEP 1: INTERNALIZE SAME CONTENT IN ZIGZAGGING SITUATIONS
  /*
    If the seq contains adjacent elements where
       add < del      (e.x. "m" & "step")       for "steps_per_bar"
         same         (e.x. "s_per_b")           -> "ms_per_beat"
       add > del ,    (e.x. "eat" & "ar")
    Internalize the "same"
  */
  #print "=== BEFORE ===\n";
  #ob_start();print_r($seq);print preg_replace('/$/m','|',ob_get_clean());
  if(0)for(;;)
  {
    $nsteps = count($seq);
    for($n=0; $n+2<$nsteps; ++$n)
    {
      if(is_array($seq[$n+0])
      && !is_array($seq[$n+1])
      && is_array($seq[$n+2]))
      {
        $add0 = $seq[$n+0][0]; $del0 = $seq[$n+0][1];
        $add1 = $seq[$n+2][0]; $del1 = $seq[$n+2][1];
        
        $dir0 = (strlen($add0) - strlen($del0));
        $dir1 = (strlen($add1) - strlen($del1));

        if( ( ($dir0<0 && $dir1>=0)
          ||  ($dir0>0 && $dir1<=0)
          ||  ($dir0<=0 && $dir1>0)
          ||  ($dir0>=0 && $dir1<0) )
        && (strlen($seq[$n+1]) <= (abs($dir0)+abs($dir1))
         || strlen($seq[$n+1] < 8))
          )
        {
          $same = $seq[$n+1];
          $rep0 = $add0.$same.$add1;
          $rep1 = $del0.$same.$del1;
          $same = '';
          while(strlen($rep0) > 0 && substr($rep0,-1,1) == substr($rep1,-1,1))
          {
            $same = substr($rep0,-1,1) . $same;
            $rep0 = substr($rep0,0, -1);
            $rep1 = substr($rep1,0, -1);
          }
          $r = Array( Array($rep0,$rep1) );
          if(strlen($same) > 0)
          {
            if(isset($seq[$n+3]) && !is_array($seq[$n+3]))
              $seq[$n+3] = $same . $seq[$n+3];
            else
              $r[] = $same;
          }
          array_splice($seq, $n, 3, $r);
          continue 2;
        }
      }
    }
    break;
  }
  #print "=== AFTER ===\n";
  #ob_start();print_r($seq);print preg_replace('/$/m','|',ob_get_clean());

  $resline = $curline;
  $strpos = 1;
  foreach($seq as $action)
  {
    if(is_array($action))
    {
      $del = strlen($action[0]);
      $add = $action[1];

      $r = DoHorizontalCursorNavigation($resline, $curx, $strpos);
      if(strlen($r))
      {
        FlushUpdown($input);
        $input .= $r;
      }
      $curx = $strpos;
      
      $replace_length = min( $del, strlen($add) );
      
      if($replace_length > 0)
      {
        // Check if we've got copypaste candidates. If so, don't use overwrite mode.
        $temp_x = $curx;
        $temp_hints = $copypaste_hints;
        $dummy = '';

        global $updown, $current_paste;
        $was_updown = $updown;
        $was_paste  = $current_paste;
        $test = DoTextEntry($temp_x, $add, $lines_above, $dummy, $temp_hints, $copypaste_curlineno);
        $current_paste  = $was_paste;
        $updown         = $was_updown;

        #$hints_ok = strpos($test, "c") === false;
        if(strpos($test, "") !== false /*&& !$hints_ok*/)
        {
          // Ignore $replace_length
        }
        else
        {
          $insertmode_wanted = false; // Overtype mode

          $del -= $replace_length;
          
          $replacement = substr($add, 0, $replace_length);
          $dummy = '';
          FlushInsertMode($input);
          $dummy_hints = Array();
          $wasx = $curx;
          $input .= DoTextEntry($curx, $replacement, false, $dummy, $dummy_hints, 0);
          
          #print "Replaced {$replacement} in $resline\n";
          $resline = substr($resline, 0, $wasx-1)
                   . $replacement
                   . substr($resline, $wasx-1 + strlen($replacement) );
          #print "Becomes $resline\n";
          
          $add = substr($add, $replace_length);
          $strpos += $replace_length;
        }
      }
      $insertmode_wanted = true; // insert mode

      if($del > 0)
      {
        $tmp_curx = $curx;
        FlushUpdown($input);
        $navigation = DoHorizontalCursorNavigation($resline, $tmp_curx, $curx + $del);
        if(strlen($navigation) + 6 < $del
        #&& !(strpos($navigation, '') || strlen($resline) > 75)
          )
        {
          FlushInsertMode($input);
          InvalidateBlock();
          $input .= 'b';   // block begin
          $input .= $navigation; // end
          $input .= 'ky'; // block end, block delete
        }
        else
        {
          $input .= str_repeat('', $del); // delete
        }
        $resline = substr($resline, 0, $curx-1)
                 . substr($resline, $curx-1 + $del);
      }

      if(strlen($add) > 0)
      {
        FlushInsertMode($input);
        $input .= DoTextEntry($curx, $add, $lines_above, $resline,
                              $copypaste_hints, $copypaste_curlineno);
        $strpos += strlen($add);
      }
    }
    else
    {
      $strpos += strlen($action);
    }
  }

  $insertmode_wanted = true; // insert mode

  #print "@$beginx -> $curx\n-'$curline'\n+'$newline'\n>'$input'\n";
  return $input;
}

// DoTextEntry is only used by EnterLine.
function DoTextEntry(&$curx, $text, $lines_above, &$result,
                     &$copypaste_hints, $copypaste_curlineno)
{
  global $updown, $current_paste;
  $input = '';
  $len = strlen($text);
  
  $before = substr($result, 0, $curx-1);
  $after  = substr($result,  $curx-1);
  
  $mark_plans = Array();
  foreach($copypaste_hints as $lineno => $pieces)
  {
    if(!is_array($pieces)) $pieces = Array($pieces);
    #print_r($copypaste_hints);
    #print_r($lines_above);
    foreach($pieces as $piece)
    {
      $r = strlen($piece);
      if($lineno != $copypaste_curlineno)
      {
        // Something should be selected from that line!
        $lineoffset = $lineno - $copypaste_curlineno; // e.g. -1
        #print "Trying to find '$piece' from a previous line (offset $lineoffset)! ".json_encode($lines_above)."\n";
        if(isset($lines_above[$lineoffset]))
        {
          $previousline = $lines_above[$lineoffset];
          $found_at = strpos($previousline, $piece);
          if($found_at !== false)
          {
            #print "Found at offset {$found_at} in '{$previousline}'\n";
            $backup_curx = $curx;

            $copy_src_x = $found_at + 1;
            $updown += $lineoffset;
            FlushUpdown($input);
            $input .= DoHorizontalCursorNavigation($previousline, $curx, $copy_src_x);
            $input .= 'kb';
            $input .= DoHorizontalCursorNavigation($previousline, $curx, $copy_src_x + $r);
            $input .= 'k';
            $current_paste = $piece;

            $updown -= $lineoffset;
            FlushUpdown($input);
            $input .= DoHorizontalCursorNavigation($result, $curx, $backup_curx);

            unset($copypaste_hints[$lineno]); // Done
          }
          else
          {
            print "�� ERROR: Unable to find '{$piece}' from previous line ('{$previousline}')!\n";
          }
        }
        else
        {
          print "�� ERROR: Unable to find '{$piece}' from previous line, because that line is not known at all!\n";
        }
      }
      elseif($lineno == $copypaste_curlineno)
      {
        // Find all possible starting positions for this $piece
        // within the current $text that we are inputting.
        $completed_line = $before . $text . $after;
        $try_begin = 0;
        for(;;)
        {
          $found_at = strpos($completed_line, $piece, $try_begin);
          if($found_at === false) break;
          $copy_src_x = $found_at + 1;
          
          $mark_plans[$copy_src_x] =
            Array('begin' => $copy_src_x,
                  'end'   => $copy_src_x + $r);
          $try_begin = $found_at + 1;
          
          // end scope for $copy_src_x
        }
        // end scope for $completed_line and $try_begin
      }
    }
  }

  $currently_marked_begin = null;
  $currently_marked_end   = null;

  for($a=0; $a<$len; ++$a)
  {
    $nspaces = FindBeginningSpaceCount($text, $a);
    if($nspaces > 1)
    {
      // Find out how many spaces would a tab character add here
      $tablen = 4 - ($curx-1) % 4;
      if($tablen <= $nspaces)
      {
        FlushUpdown($input);
        $input .= "\t";
        $curx += $tablen;
        $a    += $tablen-1;
        continue;
      }
    }

    #if(false) // COPYPASTES DISABLED
    if($lines_above !== false)
    {
      $remaining_length = $len - $a;
      
      $best_copypaste_attempt = Array
      (
        'piece'     => '',
        'yoffset'   => 0,
        'xposition' => 0,
        'penalty'   => (6/(3.0 * 6) - 0 / (0.4 * 6)) * (6*1.2) / pow(6, 1.1),
                       // Reference point for acceptable penalty
        'ok'        => false
      );

      if(strlen($current_paste) > 0
      && strlen($current_paste) <= $remaining_length
      && substr_compare($text, $current_paste, $a, strlen($current_paste)) == 0)
      {
        $best_copypaste_attempt['ok']    = true;
        $best_copypaste_attempt['piece'] = $current_paste;
        $best_copypaste_attempt['penalty'] = 0;
        #print "OK TO PASTE <{$current_paste}>\n";
      }
      #elseif(strlen($current_paste) > 0)
      #{
      #  print "NOT OK TO PASTE <{$current_paste}> at <".substr($text, $a, strlen($current_paste))."> in <".$text.">\n";
      #}

      for($r = $remaining_length; $r >= 6; --$r)
      {
        $piece = substr($text, $a, $r);
        if($piece == $current_paste) continue;

        if(preg_match('/^[ a-z]*$/', $piece) && $r <= 12) break;
        // ^ Don't bother copypasting vanilla a-z text

        $length_cost = 0;
        for($rr=0; $rr<$r; ++$rr)
          if($piece[$rr] == ' ')
            $length_cost += 4;
          elseif(preg_match('/[a-z]/', $piece[$rr]))
            $length_cost += 2.1;
          elseif(preg_match('/[A-Z]/', $piece[$rr]))
            $length_cost += 1.3;
          elseif(preg_match('/[0-9]/', $piece[$rr]))
            $length_cost += 1.1;
          else
            $length_cost += 1;
        $length_cost /= pow($r, 1.1);

        // Test whether it's found on the current line?
        $found_at = strrpos( substr($text,0,$a), $piece);
        if($found_at !== false)
        {
          $copy_src_x = $curx - ($a-$found_at);
          $lineoffset = 0;
          $penalty    = (abs($copy_src_x - $curx) / (3.0 * $r) + abs($lineoffset) / (0.4 * $r)) * $length_cost;
          if($penalty < $best_copypaste_attempt['penalty'])
          {
            $best_copypaste_attempt['piece']     = $piece;
            $best_copypaste_attempt['yoffset']   = $lineoffset;
            $best_copypaste_attempt['xposition'] = $copy_src_x;
            $best_copypaste_attempt['penalty']   = $penalty;
            $best_copypaste_attempt['ok']        = true;
          }
        }
        // How about one of the surrounding lines
        foreach($lines_above as $lineoffset => $previousline)
        {
          $try_begin = 0;
          for(;;)
          {
            $found_at = strpos($previousline, $piece, $try_begin);
            if($found_at === false) break;
            $copy_src_x = $found_at + 1;
            $penalty    = (abs($copy_src_x - $curx) / (3.0 * $r) + abs($lineoffset) / (0.4 * $r)) * $length_cost;
            if($penalty < $best_copypaste_attempt['penalty'])
            {
              $best_copypaste_attempt['piece']     = $piece;
              $best_copypaste_attempt['yoffset']   = $lineoffset;
              $best_copypaste_attempt['xposition'] = $copy_src_x;
              $best_copypaste_attempt['penalty']   = $penalty;
              $best_copypaste_attempt['ok']        = true;
            }
            $try_begin = $found_at + 1;
          }
        }
      } // Loop of remaining_lengths
      if($best_copypaste_attempt['ok'])
      {
        $piece      = $best_copypaste_attempt['piece'];
        $lineoffset = $best_copypaste_attempt['yoffset'];
        $xposition  = $best_copypaste_attempt['xposition'];
        $r = strlen($piece);

        $target_x = $curx;
        $line_so_far = $before . substr($text, 0, $a) . $after;
        $previousline = $lineoffset == 0 ? $line_so_far : $lines_above[$lineoffset];

        if($piece != $current_paste)
        {
          $updown += $lineoffset; // Go to target line
          FlushUpdown($input);
          $input .= DoHorizontalCursorNavigation($previousline, $curx, $xposition);
          $input .= 'kb';
          $input .= DoHorizontalCursorNavigation($previousline, $curx, $xposition + $r);
          $input .= 'k';
          $updown -= $lineoffset; // Go back
        }
        // Go to where we wanted to paste it
        FlushUpdown($input);
        $input .= DoHorizontalCursorNavigation($line_so_far, $curx, $target_x);
        $input .= 'c'; // copy.
        $current_paste = $piece;

        $currently_marked_begin = null;
        $currently_marked_end   = null;
        if(isset($mark_plans[$curx]))
        {
          $currently_marked_begin = $curx;
          $currently_marked_end   = $curx + $r;
        }
        $curx += $r;
        $a    += $r-1;

        continue;
      }
    }

    FlushUpdown($input);
    
    foreach($mark_plans as $plan)
    {
      $changed = false;
      if($plan['begin'] == $curx && !isset($currently_marked_begin))
      {
        $input .= "b";
        if(!isset($currently_marked_end)) $input .= "k";
        $changed = true;
        $currently_marked_begin = $curx;
      }
      if($plan['end']   == $curx
      && $plan['begin'] == $currently_marked_begin
      && (!isset($currently_marked_end) || $plan['end'] != $currently_marked_end))
      {
        if(!isset($currently_marked_begin)) $input .= "b";
        $input .= "k";
        $changed = true;
        $currently_marked_end = $curx;
      }
      if($changed)
      {
        if(isset($currently_marked_begin)
        && isset($currently_marked_end))
        {
          $was_paste     = $current_paste;
          $current_paste = substr($text, ($currently_marked_begin-1) - strlen($before),
                                  $currently_marked_end-$currently_marked_begin);
          if($current_paste != $was_paste)
          {
            --$a;
            continue 2;
          }
        }
        else
        {
          InvalidateBlock();
        }
      }
    }

    if($text[$a] == "\t") $input .= "'"; // raw character input key
    $input .= $text[$a];
    ++$curx;
    continue;
  }

  $result = $before . $text . $after;

  if(isset($copypaste_hints[$copypaste_curlineno]))
  {
    // Something should have been selected from this line
    $pieces = $copypaste_hints[$copypaste_curlineno];
    if(!is_array($pieces)) $pieces = Array($pieces);
    foreach($pieces as $piece)
    {
      if(!empty($mark_plans))
      {
        // Figure out if one of the mark_plans is already implemented.
        $best_plan = 0; $best_plan_score = -1;
        foreach($mark_plans as $r => $plan)
        {
          $score = 0;
          if(isset($currently_marked_begin) && $plan['begin'] == $currently_marked_begin)
            ++$score;
          if(isset($currently_marked_end) && $plan['end'] == $currently_marked_end)
            ++$score;
          if($score > $best_plan_score)
          {
            $best_plan_score = $score;
            $best_plan       = $r;
          }
        }
        if($best_plan_score < 2)
        {
          // Enact the missing parts of the best plan
          FlushUpdown($input);
          $plan = $mark_plans[$best_plan];
          if(!isset($currently_marked_begin) || $plan['begin'] != $currently_marked_begin)
          {
            $input .= DoHorizontalCursorNavigation($result, $curx, $plan['begin']);
            $input .= 'b';
          }
          if(!isset($currently_marked_end) || $plan['end'] != $currently_marked_end)
          {
            $input .= DoHorizontalCursorNavigation($result, $curx, $plan['end']);
            $input .= 'k';
          }
        }
        $current_paste = $piece;
        unset($copypaste_hints[$copypaste_curlineno]); // Done
      }
    }
  }

  return $input;
}

function isalnum($c) // isalnum_ from main.cc
{
  if(is_string($c)) $c = ord($c);
  return ($c >= 65 && $c <= 90)  // A-Z
      || ($c >= 97 && $c <= 122) // a-z
      || ($c >= 48 && $c <= 57)  // 0-9
      || ($c == 95); // _
}
function isspace($c)
{
  if(is_string($c)) $c = ord($c);
  return $c == 32 || ($c >= 9 && $c <= 13);
}
function ispunct($c) // ispunct_ from main.cc
{
  if(is_string($c)) $c = ord($c);
  return !isspace($c) && !isalnum($c);
}
function isspace_punct($c) // isspace(c) || ispunct_(c) from main.cc
{
  return !isalnum($c);
}

function DoHorizontalCursorNavigation($text, &$curx, $goalx)
{
  $input = '';
  if($text === false) $text = '';

  $indent = FindBeginningSpaceCount($text) + 1;
  $end_x = strlen($text) + 1;

  for($method=0; $method<4; ++$method)
  {
    $in   = '';
    $x    = $curx;
    $cost = 0;

    switch($method)
    {
      case 0:
        break;
      case 1:
        // Use ^A (home) once
        $in .= ''; $cost += 1;
        $x = ($x == $indent ? 1 : $indent);
        break;
      case 2:
        // Use ^A (home) twice
        $in .= ''; $cost += 2;
        $x = ($x == $indent ? 1 : $indent);
        $x = ($x == $indent ? 1 : $indent);
        break;
      case 3:
        // Use ^E (end) once to get to the end
        if($x > $end_x) { $in .= ''; $cost += 0.7; } // left = a bit cheaper
        else            { $in .= ''; $cost += 1; }
        $x  = $end_x;
        break;
    }

    // Speculatively use either ctrl-left until
    // it cannot get us closer to the goal
    while($x != $goalx)
    {
      $distance = abs($goalx-$x);
      $endx  = $end_x - 1;

      $choice = null;
      if($x > $goalx)
      {
        // Where would a ctrl-left get us?
        $testx = $x - 1;
        // First go one left.
        if(--$testx < 0) goto ctrl_left_nok;
        // Skip possible space.
        while(/*$testx > 0 &&*/ ($testx >= $endx || isspace_punct($text[$testx]))) { if(--$testx < 0) goto ctrl_left_nok; }
        // Then skip to the beginning of the current word
        while(/*$testx > 0 &&*/ !($testx >= $endx) && isalnum($text[$testx])) { --$testx; }
        // Then undo the last k_left, unless we're at the beginning of the file
        ++$testx; // HACK: true simulates linenumber test
        
        $testx = $testx + 1;
        $newdistance = abs($goalx - $testx);
        if($newdistance < (isset($choice) ? $choice[3] : $distance))
          $choice = Array(' s', 1, $testx, $newdistance); // ctrl-left, cost=1, new $x, new distance
ctrl_left_nok:;
      }
      if($x < $goalx)
      {
        // Where would a ctrl-right get us?
        $testx = $x - 1;
        if($testx == $endx) goto ctrl_right_nok;
        // First skip possible space
        while(/*$testx < $endx &&*/ isspace_punct($text[$testx])) { if(++$testx >= $endx) goto ctrl_right_nok; }
        // Then skip to the end of the current word
        while($testx < $endx && isalnum($text[$testx])) ++$testx;

        $testx = $testx + 1;
        $newdistance = abs($goalx - $testx);
        if($newdistance < (isset($choice) ? $choice[3] : $distance))
          $choice = Array(' t', 1, $testx, $newdistance); // ctrl-right, cost=1, new $x, new distance
ctrl_right_nok:;
      }
      if(!isset($choice)) break;
      
      // If the choice covers just 1 space, replace its input with ^B or ^F
      /**/if($choice[2] == ($x-1)) { $choice[0] = ''; $choice[1] = 0.7; }
      elseif($choice[2] == ($x+1)) { $choice[0] = ''; $choice[1] = 0.7; }

      // If the cost of the choice is greater than the cost of just
      // using arrow keys from where are now, discard it
      $cost_arrows_from_now = 0.7 * (abs($goalx - min($end_x,$x)) + ($x > $end_x ? 1 : 0));
      if($choice[1] + abs($goalx - $choice[2])*0.7 > $cost_arrows_from_now)
        break;
      $in    .= $choice[0];
      $cost  += $choice[1];
      $x      = $choice[2];
    }

    // Use ^B/^F (left/right) to cover the rest of the distance
    if($x > $end_x) { $in .= ''; $cost += 0.7; $x = $end_x; }
    $in   .= str_repeat($x < $goalx ? '' : '', abs($goalx-$x));
    $cost += abs($goalx-$x) * 0.7;
    $x    = $goalx;

    if(!isset($best) || $cost < $best['cost'])
      $best = compact('in', 'x', 'cost');
  }
  $curx  = $best['x'];
  return $best['in'];
}

function DoVerticalCursorAndWindowNavigation(
  &$curx, &$cury, &$winy,
  $goal_cursor_y, $goal_window_y,
  &$input)
{
  global $dimy, $updown;
  $best = null;
  
  for($method=0; $method<16; ++$method)
  {
    #print "DoVerticalCursorAndWindowNavigation/$method: curx=$curx, cury=$cury, winy=$winy\n";
    $in = '';
    $up = $updown;
    $x = $curx;
    $y = $cury;
    $w = $winy;
    $cost = 0;
    if($method & 1)
    {
      // First use ^KU to get to 1,1
      if($goal_window_y != 1 && $w == $goal_window_y)
      {
        // Don't even consider this option if it moves the window
        continue;
      }
      $in .= 'u';
      $x = 1; $up = 0; // ignore $updown
      $y = 1; $w = 1;
      $cost += 1;
    }
    if($method & 2)
    {
      // First use ^KL to get to specific line
      $oldy = $y;
      $x = 1; $up = 0; // ignore $updown
      
      $dy = (int)( $dimy/2 );

      /**/$outside_range = ($w > $goal_cursor_y) || (($w + $dimy) <= $goal_cursor_y);
      if($outside_range)
      {
        // Try to jump to such line that when the window is repositioned,
        // the top of the window is already exactly where we want it to be.
        $choose_cursor_y = $goal_window_y;
        if($y > $oldy)
          $choose_cursor_y = $goal_window_y + $dy;

        $y = $choose_cursor_y;
        $outside_range = ($w > $y) || (($w + $dimy) <= $y);
        if($outside_range)
        {
          $in .= 'l' . ($choose_cursor_y) . "\r";
          $cost += 60; // Add an artificial cost for ^Kl to discourage it over short scrolls
        }
      }
      if(!$outside_range)
      {
        // Jump to that line.
        $in .= 'l' . ($goal_cursor_y) . "\r";
        $y = $goal_cursor_y;
        $outside_range = ($w > $y) || (($w + $dimy) <= $y);
        $cost += 60; // Add an artificial cost for ^Kl to discourage it over short scrolls
      }
      if($outside_range)
      {
        $w = $y > $oldy
          ? ( ($y/*-1*/) > $dy
              ? $y - $dy
              : 1)
          : $y;
        #print "After <$in>, winy=$w as dimy=$dimy\n";
      }
      $cost += 3 + 2*strlen($goal_cursor_y);
    }
    if($method & 4)
    {
      $in .= ' w'; // Ctrl-home = goto beginning of window (vertically)
      $up = 0; // ignore $updown
      $y = $w;
      $cost += 1;
    }
    if($method & 8)
    {
      $in .= ' u'; // Ctrl-end = goto end of window (vertically)
      $up = 0; // ignore $updown
      $y = $w + $dimy;
      $cost += 1;
      #print "method $method: w=$w, y becomes $y\n";
    }

    while($goal_cursor_y < $y)
    {
      $up -= 1;
      $y -= 1;
      $w = min($y, $w);
      $cost += 1;
      if(isset($best) && $cost > $best['cost']) continue 2;
    }
    while($goal_window_y < $w)
    {
      FlushThisUpdown($in, $up);
      $in .= '';
      --$w;
      $cost += 1;
      if(isset($best) && $cost > $best['cost']) continue 2;
    }
    while($goal_window_y > $w && $goal_cursor_y > $y)
    {
      FlushThisUpdown($in, $up);
      $in .= '';
      ++$w;
      $y = max($y, $w);
      $cost += 1;
    }
    while($goal_cursor_y > $y)
    {
      $up += 1; $y += 1;
      $cost += 1;
      if($y >= $w + $dimy) $w = $y - $dimy+1;
      
      if($y > $w && $w < $goal_window_y)
      {
        FlushUpdown($in, $up);
        $in .= ''; ++$w;
        $cost += 1;
      }
      if(isset($best) && $cost > $best['cost']) continue 2;
    }
    while($goal_window_y > $w)
    {
      FlushThisUpdown($in, $up);
      $in .= '';
      ++$w;
      $cost += 1;
    }
    while($goal_window_y < $w)
    {
      FlushThisUpdown($in, $up);
      $in .= '';
      --$w;
      $cost += 1;
      if(isset($best) && $cost > $best['cost']) continue 2;
    }

    if(!isset($best) || $cost < $best['cost'])
      $best = compact('in', 'up', 'x', 'y', 'w', 'cost');
  }	
  #print "Navigated from $curx,$cury,$winy to {$best['x']},{$best['y']},{$best['w']} using {$best['in']}+{$best['up']}\n";
  
  $input .= $best['in'];
  $curx  = $best['x'];
  $cury  = $best['y'];
  $winy  = $best['w'];
  $updown = $best['up'];
}

function CreateFSeq($yaxis, $xaxis)
{
  return str_repeat( ($xaxis > 0 ? F4 : F3), abs($xaxis) )
       . str_repeat( ($yaxis > 0 ? F2 : F1), abs($yaxis) );
}

function Saving()
{
  static $name_given = false;
  if(!$name_given) { $name_given=true; return "d��" . SAVE_FILE . "\r"; }
  return "d\n";
}

/////////////
function SplitLine($line)
{
  $counts = preg_split('/`/', preg_replace("/\t.*/",     '', $line));
  $codes  = preg_split('/`(?=[-0-9A-F]+:)/', preg_replace("/^[^\t]*\t/", '', $line));
  $prev='';
  foreach($counts as &$c)
  {
    $c = trim($c) . $prev;
    $prev = '`';
  }
  unset($c);

  $rescodes = Array();
  foreach($counts as $index => $c)
  {
    $s = '';
    foreach($codes as $c2)
    {
      if(!preg_match('/^([-0-9A-F]+):(.*)/', $c2, $mat))
        $mat = Array('', '0-9A-F', $c2);

      // TODO: Add support for "0-F" rather than "0-9A-F"
      if(preg_match("/[{$mat[1]}]/", strtoupper(dechex($index))))
        $s .= $mat[2];
    }
    $rescodes[$index] = strtok($s, "\r\n");
  }
  return Array($counts, $rescodes);
}


$lines = file(IN_FILE);
$levels = Array();
foreach($lines as $line)
{
  $tmp = SplitLine($line);
  #print_r($tmp);
  foreach($tmp[0] as $c)
    if(strlen($c)
    && $c[0] >= '0' && $c[0] <= '9')
    {
      $step = (int)$c;
      $levels[$step] = $step;
    }
}

foreach($screens as $level=>$dummy)
  if(!isset($levels[$level]))
    print "�� ERROR: No level $level (screen mode would be set)\n";
foreach($line_anchor as $level=>$dummy)
  if(!isset($levels[$level]))
    print "�� ERROR: No level $level (scrolling position would be set)\n";
foreach($selections as $level=>$dummy)
  if(!isset($levels[$level]))
    print "�� ERROR: No level $level (would select text)\n";
$cur_screen = Array(0,0);

$program = Array();
sort($levels);
$anchor = 1;
$cap    = 24;
$width  = 80;
$wy = 1; $ey = 1;
if(1)foreach($levels as $level)
{
  $p = Array
  (
    'modechanges' => '',
    'window_y'   => @$line_anchor[$level],
    'selections' => @$selections[$level],
    'cursor_y'   => -1,
    'num_lines'  => 0,
    'text'       => Array()
  );

  $s = &$p['selections'];
  if(isset($s) && !is_array($s)) $s = Array(0 => $s);
  unset($s);

  if(isset($screens[$level]))
  {
    $p['modechanges'] = $screens[$level][0];
    $p['lines']       = $screens[$level][2] - 1;
    
    $p['reset'] = isset($screens[$level]['reset']);
    
    $room = ($wy + ($p['lines']-2)) - $ey;
    #if($room < 0)
    #  print "**** PROBLEM WITH $level : wy=$wy, ey=$ey, lines={$p['lines']}, room=$room\n";
    #else
    #  print "**** OK $level : wy=$wy, ey=$ey, lines={$p['lines']}, room=$room\n";
  }

  $lineno = 1;
  $relevant_lines = Array();
  $skip = 0;
  $something_edited = false;
  $move_delete = Array();
  foreach($lines as $line)
  {
    $tmp = SplitLine($line);
    $relevant = null;
    $edited   = false;
    $incomplete = false;
    foreach($tmp[0] as $subphase => $ctrl)
    {
      $text   = $tmp[1][$subphase];
      preg_match("@^([0-9]*)([^\t]*)@", $ctrl, $mat);
      $step   = $mat[1];
      if($step == '') $step = 99999;
      $effect = $mat[2];
      $erase_line = false;

      if($level >= $step)
      {
        $erase_line  = substr_count($effect, '*') != 0;
        $revisioning = substr_count($effect, '`') != 0;
        $copy_from   = substr_count($effect, '^') - substr_count($effect, 'v');
      }

      if($level == $step)
      {
        if($p['cursor_y'] == -1)
          $p['cursor_y'] = $lineno;

        $d = Array('copy_from' => $copy_from, 'text' => $text);

        if($revisioning) $d['revisioning'] = true;
        if($erase_line) $d['erase_line'] = true;
        if($skip) { $d['skip'] = $skip; $skip = 0; }
        $d['at'] = $line;
        $p['text'][] = $d;
        $p['num_lines'] += 1;

        $ey = $lineno;
        $edited = true;
      }
      
      if($level >= $step)
        $relevant = $erase_line ? null : $text;
      else
        $incomplete = true;
    }
    if($edited)
      $something_edited = true;
    if(isset($relevant))
    {
      $relevant_lines[$lineno++] = Array($edited, $relevant, $incomplete);
      if(!$edited && $something_edited)
        ++$skip;
    }
  }
  if(!empty($move_delete))
  {
    foreach($move_delete as $lineno) unset($relevant_lines[$lineno]);
    $relevant_lines[0] = null; ksort($relevant_lines);
    $relevant_lines = array_values($relevant_lines);
    unset($relevant_lines[0]);
    #print_r($relevant_lines);
  }
  
  // If there is a line-delete followed by enter-new-line,
  // change the line-delete into a line-edit.
  if(COMPRESS_DELETE_NEWLINES) // scope
  {
    foreach($p['text'] as $itemno => $d)
    {
      if(isset($d['erase_line'])
      && isset($p['text'][$itemno+1])
      && !isset($p['text'][$itemno+1]['revisioning']))
      {
        $p['text'][$itemno]['revisioning'] = true;
        $p['text'][$itemno]['text']        = $p['text'][$itemno+1]['text'];
        unset($p['text'][$itemno]['erase_line']);
        unset($p['text'][$itemno+1]);
      }
    }
    $p['text'] = array_values($p['text']);
  }

  $code = '';
  if(isset($line_anchor[$level]))
  {
    $anchor = $p['cursor_y'] - $line_anchor[$level];
    if($anchor < 1) $code .= "ERROR: ";
    $code .= "ANCHOR SET TO {$anchor}, {$cap} LINES\n";
  }
  $wy = $anchor;

  $p['window_y'] = $anchor;

  if(isset($screens[$level]))
  {
    $cap   = $screens[$level][2] - 1;
    $width = $screens[$level][1];
  }

  $which_edit_line = -($p['cursor_y']-1);
  $edit_span_counter = 0;
  $last_edit_line    = 0;
  $first_edit_line   = false;
  foreach($relevant_lines as $lineno => $text)
  {
    $incomplete = $text[2];
    $in_edit = $text[0];
    $text    = $text[1];
    
    $in_window = ($lineno >= $anchor && $lineno < $anchor+$cap);
    
    $code_line = '';

    if($in_edit)
    { 
      if(!$in_window) $code_line .= "\33[31m";
      #$code_line .= "MARK";
      $code_line .= '|';
      if($in_window) $code_line .= "\33[0;32m";
      if($edit_span_counter == 0) ++$edit_span_counter;
      if($edit_span_counter == 2) ++$edit_span_counter;
      $last_edit_line = $lineno-$anchor;
      if($first_edit_line === false) $first_edit_line = $lineno-$anchor;
    }
    else
    {
      if($edit_span_counter == 1) ++$edit_span_counter;
      if($edit_span_counter == 3) ++$edit_span_counter;
      $code_line .= ' ';
      if($in_window)
        $code_line .= "\33[0;32m";
      else
        $code_line .= "\33[1;30m";
    }
    /*
    $code_line .= ($lineno == $anchor) ? ">" :
            (($lineno == $anchor+$cap-1) ? "_" :
            (($lineno == $anchor-1) ? "_" :
            (($lineno == $anchor+$cap) ? "^" : " ")));
    */

    $code_line .= sprintf("%-5s%s", sprintf('%3d', $lineno), $incomplete ? '+' : ' ');
    $code_line .= sprintf("%4d,%3d ", $p['cursor_y'] - $lineno, $lineno-$anchor);

    if($lineno >= $anchor-PLAN_SHOW_OUTSIDES
    && $lineno < $anchor+$cap+PLAN_SHOW_OUTSIDES)
    {
      $text = str_replace("\r",'', $text);
      $text = str_replace("\n",'', $text);
      $length = strlen($text);
      $before = "";
      $middle = "";
      $after  = "";
      if($in_window)
      {
        $before = "\33[0m";
        if($length > $width)
        {
          $middle = substr($text, 0, $width);
          $middle .= "\33[1;31m";
          $middle .= substr($text, $width);
        }
        else
        {
          $middle = $text;
        }
      }
      else
      {
        $before .= "\33[0;33m";
        if($in_edit && $length > $width)
        {
          $middle = substr($text, 0, $width);
          $middle .= "\33[1;31m";
          $middle .= substr($text, $width);
        }
        else
        {
          $middle = $text;
        }
      }
      $after .= "\33[0m\n";
      
      if(isset($p['selections']) && isset($p['selections'][$which_edit_line]))
      {
        $substring = $p['selections'][$which_edit_line];
        if(is_array($substring)) foreach($substring as $substring)
          $middle = str_replace($substring, "\33[44m{$substring}\33[40m", $middle);
      }

      $code_line .= $before . $middle . $after;

      $code .= $code_line;
    }
    
    ++$which_edit_line;
  }
  if($edit_span_counter >= 3)
    $code = "\33[1;31mERROR: GAPPY EDITS\n" . $code;

  if($last_edit_line == $cap-1)
    $code = "\33[1;31mERROR: UNSAFE LAST LINE OF EDIT\n" . $code;

  if($last_edit_line >= $cap || $first_edit_line < 0)
    $code = "\33[1;31mERRORS: EDIT OUTSIDE VIEW\n" . $code;

  #$ly = $line_anchor[$level];
  #$line_anchor[$level] = Array($ly, $p['cursor_y']-$ly);

  $program[$level] = $p;
  if($SHOW_PLAN && $level >= $FIRST_PLAN)
  {
    print "------------------ $level ----------------\n".str_replace("\r","",$code)."\n\n";
  }
}
#print_r($program);
#foreach($line_anchor as $k=>$v)
#  print "s@  $k => {$v[0]}@  $k => {$v[1]} /* changed */@;";
if($SHOW_PLAN)
  exit;

$curx = 1;
$cury = 1;
$winy = 1;
$dimy = 25;

$result = Array(1 => "", 2 => "", 3 => "");

$input = '';

if(1)foreach($program as $level => $p)
{
  if($level >= $MAX_LEVEL) break;
  $TURBO = false;
  
  if(isset($turbo_ranges[$level])) $TURBO = $turbo_ranges[$level];
  
  #if($level < 40000) $TURBO = 1; // HACK

  $def_speed = DEFAULT_SPEED;
  $com_speed = COMMENT_SPEED;
  if(FORCE_SPEED !== false) $TURBO = FORCE_SPEED;

  if($cury < $winy + $dimy-3 && $input != '' && count($p['text']) > 1)
  {
    if(isset($p['lines']) || $p['window_y'] != $winy)
    {
      FlushUpdown($input);
      $input .= Saving();
      # $input .= "��"; // sleep 5/8 seconds
    }
  }
  
  if(array_search($level, $bigdelay) !== false && FORCE_SPEED === false 
    )
  {
    FlushUpdown($input);
    $input .= "��" . Saving();
    $input .= DELAY4s;
    $input .= $def_speed;
  }
  
  if(isset($p['lines']))
  {
    if($p['reset'])
    {
      $input = ''; $updown = 0;
      $insertmode_wanted=$insertmode_current=true;
    }
    
    if(strlen($p['modechanges']) > 0)
    {
      FlushUpdown($input);
      FlushInsertMode($input);
      $input .= $p['modechanges'];
    }
    //if($dimy == 0) $input .= "~";
    $dimy = $p['lines'];

    if($p['reset'])
    {
      $curx = 1;
      $cury = 1;
      $winy = 1;
    }

    /* $cury = 9999; */
    if($cury >= $winy + $dimy - 1)
    {
      print "\n\nSTATE $level: cury=$cury, winy=$winy, dimy=$dimy\n\n";
    }
  }
  
  
  /* NAVIGATE THE WINDOW EDGES (ALSO CURSOR) */
  $goal_cursor_y = $p['cursor_y'];
  #if($goal_cursor_y > $p['window_y'])
  #  --$goal_cursor_y;
  $goal_window_y = $p['window_y'];

  #print "Doing vertical cursor and window navigation. Currently $curx,$cury, winy=$winy, dimy=$dimy; goal: cursy=$goal_cursor_y winy=$goal_window_y "; $inlen = strlen($input);
  DoVerticalCursorAndWindowNavigation(
    $curx, $cury, $winy,
    $goal_cursor_y,
    $goal_window_y,
    $input);
  #print "- outcome: $curx,$cury, winy=$winy; input: ".substr($input, $inlen)."\n";

  if($winy != $p['window_y'])
  {
    print "Eh, what? winy($winy) window_y({$p['window_y']})\n";
  }
  if($cury != $p['cursor_y'])
  {
    print "Eh, what? cury($cury) cursor_y({$p['cursor_y']})\n";
  }

  /* ENTER TEXT LINES */

  $remaining = count($p['text']);
  $n_already_copied = 0;
  
  $selections = Array();
  if(isset($p['selections']))
    $selections = $p['selections'];

  $tmp = $cury;
  $erasures = Array();
  $ignore_erasures = Array();
  #print_r($p);
  foreach($p['text'] as $lineid => $line_to_add)
  {
    if(isset($line_to_add['skip']))
      $tmp += $line_to_add['skip'];
    if(isset($line_to_add['erase_line']))
      $erasures[] = $tmp;
    else
      ++$tmp;
  }
  #print_r($erasures);
  #print_r($result);

  foreach($p['text'] as $lineid => $line_to_add)
  {
    if(isset($line_to_add['skip']))
    {
      $goal_cursor_y += $line_to_add['skip'];
      $updown        += $line_to_add['skip'];
      $cury          += $line_to_add['skip'];
    }
    $which_edit_line = $goal_cursor_y - $p['cursor_y'];
    #print "-- level=$level -- lineid=$lineid = which_edit_line=$which_edit_line (p:cursor_y={$p['cursor_y']}, goal_cursor_y=$goal_cursor_y)\n";
    #print_r($ignore_erasures);

    --$remaining;

    if($TURBO !== false)
      $input .= "��".chr($TURBO);
    else
      $input .= $def_speed;
    
    $copy_where  = $line_to_add['copy_from'];
    $revisioning = isset($line_to_add['revisioning']);
    $erase_line  = isset($line_to_add['erase_line']);
    
    if($erase_line && in_array($goal_cursor_y, $ignore_erasures))
    {
      #print "Ignoring erasure of line $goal_cursor_y, already done by moving\n";
      foreach($ignore_erasures as $n=>$m)
        if($m == $goal_cursor_y)
          { unset($ignore_erasures[$n]); break; }
      continue;
    }

    $spaces_want = FindBeginningSpaceCount( $line_to_add['text'] );
    $spaces_have = null;

    $cur_line = $result[$cury];
    if($n_already_copied || $revisioning || $erase_line)
    {
      // EDIT CURRENT LINE
      $updown += ($goal_cursor_y) - $cury;
      $cury   += ($goal_cursor_y) - $cury;
      $cur_line    = $result[$cury];
      $spaces_have = FindBeginningSpaceCount( $cur_line );

      // Check whether we are doing indenting.
      if($spaces_want > $spaces_have)
      {
        for($n_indented = 0; ; ++$n_indented)
        {
          if(!isset($p['text'][$lineid + $n_indented]['revisioning'])) break;
          if($p['text'][$lineid + $n_indented]['text'] != str_pad('', $spaces_want - $spaces_have) . $result[$cury + $n_indented]) break;
        }
        if($n_indented > 1)
        {
          FlushUpdown($input);
          $input .= DoHorizontalCursorNavigation( $cur_line, $curx, 1);
          $input .= '';
          $updown += $n_indented;
          FlushUpdown($input);
          $input .= '';
          while($spaces_want-$spaces_have >= 4)
          {
            $input .= '.';
            $spaces_have += 4;
            for($n=0; $n<$n_indented; ++$n)
              $result[$cury+$n] = '    ' . $result[$cury+$n];
          }
          $cury += $n_indented;
          $updown += ($goal_cursor_y) - $cury;
          $cury   += ($goal_cursor_y) - $cury;
          $cur_line = $result[$cury];
        }
      }
    }
    elseif($copy_where)
    {
      $copy_n     = 1;
      $copy_times = 1;
      
      for($max=1; $max<200; ++$max)
        if(!isset($p['text'][$lineid+$max])
        || !isset($p['text'][$lineid+$max]['copy_from'])
        ||        $p['text'][$lineid+$max]['copy_from']==0 )
          break;

      for($n=1; $n<=$max; ++$n)
      {
        $skip = 0;
        $a=0;
        // How many times we might copy $n lines?
        for($at=1; $at*$n <= $max; ++$at)
        {
          // Iterate through $n lines
          for($an=0; $an<$n; ++$an)
          {
            $l = $p['text'][$lineid + $a++];

            if(isset($l['skip']))
            {
              if($at > 1) $skip += $l['skip'];
              if($an > 0) break 2; // This option is not possible
            }

            if($l['copy_from'] != $copy_where + $skip)
            {
              #print "break at=$at, an=$an/$n, got {$l['copy_from']} expect $copy_where + $skip\n";
              break 2;
            }
          }
          $skip += $n;
        }
        if(--$at <= 0) continue;
        #print "Could copy $n, $at times (a = $a, max = $max)\n";
        if($n * $at > $copy_n * $copy_times
        || ($n * $at == $copy_n * $copy_times
        && $n + $at < $copy_n + $copy_times))
        {
          $copy_n     = $n;
          $copy_times = $at;
        }
      }
      if($copy_where < 0) $copy_where += $copy_n * $copy_times;
      
      #print "Copying $copy_n, $copy_times times\n";

      // CREATE A COPY OF THE PREVIOUS LINE
      // Go to copy_where
      $updown += ($goal_cursor_y-$copy_where) - $cury;
      $cury   += ($goal_cursor_y-$copy_where) - $cury;
      FlushUpdown($input);
      // Go to beginning of that line
      $sourcelines = Array();
      for($n=0; $n<$copy_n; ++$n)
        $sourcelines[] = $result[$cury + $n];

      // Check if this is a move request
      $m = $goal_cursor_y - $copy_where;
      if($copy_where < 0) $m += $copy_times * $copy_n;
      else $m -= $copy_times * $copy_n;
      $copy_move = false;
      for($n=0; $n<$copy_n; ++$n)
      {
        #print "Is copy move? level=$level, $m=$m\n"; 
        if(in_array($m, $erasures))
        {
          $copy_move = true;
          $ignore_erasures[] = $m;
        }
      }

      $cur_line = $sourcelines[0];
      $input .= DoHorizontalCursorNavigation( $cur_line, $curx, 1);
      $input .= 'b';
      // Go to the end of the copy-section, mark end of block
      $updown += $copy_n; $cury += $copy_n;
      FlushUpdown($input);
      $input .= 'k';
      // Go to target location
      $updown += ($goal_cursor_y) - $cury;
      $cury   += ($goal_cursor_y) - $cury;
      FlushInsertMode($input);
      // Paste
      for($m=0; $m<$copy_times; ++$m)
      {
        if($m > 0 && isset($p['text'][$lineid + $m*$copy_n]['skip']))
        {
          $updown += $p['text'][$lineid + $m*$copy_n]['skip'];
          $cury   += $p['text'][$lineid + $m*$copy_n]['skip'];
        }
        FlushUpdown($input);
        if($copy_move) $input .= 'm'; else $input .= 'c'; // Automatically moves cursor down by n lines
        InsertLines($result, $cury, $sourcelines);
        $cury += $copy_n; // Note: In Joe, cursor does not move when copypasting
      }
      $input .= 'bk';
      InvalidateBlock();
      // Go to target location again
      $updown += ($goal_cursor_y) - $cury;
      $cury   += ($goal_cursor_y) - $cury;
      // If it was a move, delete the source lines
      if($copy_move)
      {
        $m = $goal_cursor_y - $copy_where;
        if($copy_where < 0) $m += $copy_times * $copy_n;
        else $m -= $copy_times * $copy_n;
        #print "goal $goal_cursor_y, where $copy_where\n";
        #print_r($result);
        EraseLines($result, $m, $copy_n);
        #print_r($result);
      }

      #print "��\rLEVEL $level, COPYING $copy_n lines, $copy_times times, offset $copy_where\n";
      #print_r($result);
      
      $spaces_have = FindBeginningSpaceCount( $cur_line );
      
      $n_already_copied = $copy_n * $copy_times;
    }
    else // inserting a new line that is not a copy
    {
      // Cursor may currently be in the end of previous line (normally),
      // OR at the target line (if we just used window-navigation)

      $spaces_have = FindBeginningSpaceCount( $cur_line );
      // INSERT A BLANK LINE
      if($spaces_have > 0 && $spaces_want == 0)
      {
        // Go to target line and hit enter in the beginning
        $updown += ($goal_cursor_y - $cury);
        $cury     = $goal_cursor_y;
        $cur_line = $result[$cury];
        $spaces_have = FindBeginningSpaceCount( $cur_line );
      }
      if($cury == $goal_cursor_y - 1)
      {
        if($spaces_have < $spaces_want && FindBeginningSpaceCount($result[$cury+1]) >= $spaces_want)
        {
          // Go to text-beginning of next line and hit enter
          $updown += 1;
          $cur_line = $result[++$cury];
          $spaces_have = $spaces_want; //FindBeginningSpaceCount( $cur_line );
          FlushUpdown($input);
          FlushInsertMode($input);
          $input .= DoHorizontalCursorNavigation($cur_line, $curx, $spaces_want+1);
          $input .= "\r";
          $updown -= 1;
        }
        else
        {
          // Go to end of line, and hit enter
          FlushUpdown($input);
          FlushInsertMode($input);
          $input .= DoHorizontalCursorNavigation($cur_line, $curx, strlen($cur_line)+1);
          $input .= "\r";
          ++$cury;
        }
        // We accomplished a line containing spaces
        $cur_line = str_repeat(' ', $spaces_have);
        $curx = $spaces_have + 1;
        InsertLine($result, $cury, $cur_line);
      }
      else
      {
        // Go to beginning of that line
        //if($curx != $spaces_have+1 || $spaces_want == 0)
        {
          FlushUpdown($input);
          $input .= DoHorizontalCursorNavigation($cur_line, $curx, 1);
          $spaces_have = 0;
        }
        // Hit enter, and up
        FlushUpdown($input);
        FlushInsertMode($input);
        $input .= "\r"; $updown -= 1;
        // We accomplished a line containing spaces
        $cur_line = str_repeat(' ', $spaces_have);
        $curx = strlen($cur_line) + 1;
        InsertLine($result, $cury, $cur_line);
      }
    }
    if($n_already_copied > 0)
      --$n_already_copied;

    if($erase_line)
    {
      FlushUpdown($input);
      //FlushInsertMode($input); -- not needed here
      $input .= "";
      $curx = 1;
      #print "-- erasing line, cury=$cury and goal_cursor_y=$goal_cursor_y\n";
      #print_r($result);
      EraseLines($result, $cury, 1);
      continue;
    }

    if($spaces_want == 0 && $spaces_have >= 8 && strlen($cur_line) == $spaces_have)
    {
      FlushUpdown($input);
      FlushInsertMode($input);
      $input .= "\r"; // ctrl-Y, enter
      $spaces_have = 0;
      $cur_line = '';
      $curx = 1;
      if($line_to_add['text'] != '')
        $updown -= 1; // up
      else
      {
        // FIXME: Explain what this does
        $result[$cury++] = $line_to_add['text'];
        ++$goal_cursor_y;
        continue;
      }
    }

    // Produce indentation-eating input (relies on "smartbacks" feature in ftyperc):
    while($curx == $spaces_have + 1 && $spaces_have > $spaces_want)
    {
      FlushUpdown($input);
      //FlushInsertMode($input); -- not needed here
      $input .= '';
      $eat = (1 + ($curx-2) % 4);
      $curx -= $eat;
      $spaces_have -= $eat;
      $cur_line = substr($cur_line, $eat);
    }

    $above = Array();
    $max_mag = max(5,min($cury-$winy, $dimy-($cury-$winy))-1);
    for($mag=1; $mag<=$max_mag; ++$mag)
      for($offset=-$mag; $offset<=$mag; $offset += $mag*2)
        if(isset($result[$cury + $offset]))
          $above[$offset] = $result[$cury + $offset];

    #print_r($above);
    #print_r($selections);
    #print "-- ENTERING <{$line_to_add['text']}>, LINE ALREADY <{$cur_line}>\n";
    $inputline = EnterLine($line_to_add['text'], $cur_line, $curx, $above,
                           $selections, $which_edit_line);
    #print "-- PRODUCED <$inputline>\n";
    if($TURBO === false)
    {
      $inputline = preg_replace('@^//([^�]*)$@', '//'.$com_speed.'\1'.$def_speed, $inputline); // Make comments go quick
      $inputline = preg_replace('@//([^�]*)$@', '//'.$com_speed.'\1'.$def_speed, $inputline); // Make comments go quick
      $inputline = preg_replace('@/\*([^�]*)\*/$@', '/*'.$com_speed.'\1'.$def_speed.'*/', $inputline); // Make comments go quick
      #$inputline = preg_replace('@^\'([^�]*)$@', '\''.$com_speed.'\1'.$def_speed, $inputline); // Make comments go quick
      #$inputline = preg_replace('@\'([^�]*)$@', '\''.$com_speed.'\1'.$def_speed, $inputline); // Make comments go quick
    }

    $input .= $inputline;

    $result[$cury] = $line_to_add['text'];
    ++$goal_cursor_y;
  }

  if(isset($p['selections']))
  {
    #$input .= "�� bk";
    #InvalidateBlock();
  }
  
  ksort($result);

  #print "-- $level completed --\n";
  #print_r($p);
  #print_r($result);
}


if($FINAL_OUTPUT)
{
  ksort($result);
  print join("\n", $result);
  exit;
}

$input .= Saving();

$input = preg_replace('/([cm.])((?:[^]|[dlu]){60,})b/', '$1bk$2b', $input);

$input = preg_replace('/(��.)+(��.)/', '$2', $input);
for(;;)
{
  $input2 = preg_replace('/(��.)([^�]*)\1/', '$1$2', $input);
  if($input2 == $input) break;
  $input = $input2;
}

print ATFIRST;
print $input;
print ATLAST;
