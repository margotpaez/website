<?php
  /**
  * Info Plugin: Displays information about various DokuWiki internals
  *
  * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
  * @author     Emanuele <emanuele45@interfree.it>
  */
$meta['ws_excludedns']    = array('string');
$meta['ws_excludedns_pattern'] = array('string');
$meta['ws_cacheresults']=array('onoff');
$meta['ws_cacheexpire']=array('numeric','_pattern'=>'/[0-9]{1,10}/');
//$meta['ws_topcontrib']=array('numeric');
