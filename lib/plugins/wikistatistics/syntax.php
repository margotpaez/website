<?php
 /**
 * Wiki Statistics Plugin: Displays some wiki stats
 *
 * @license             GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @original author     Paco Avila (Monkiki) <monkiki@gmail.com>
 * @author              Emanuele <emanuele45@interfree.it>
 * @contributor         Thomas <thomas(dot)delhomenie(at)gmail(dot)com>
 * @patched by          Matthieu <matthieu(dot)rioteau(at)skf(dot)com>
 *                          (2009/11/10)  - Patch correct bad behavior in "bymonth" function where comparison of months considered October (#10) to be lesser than February (#2) because of leading zeros suppression
 *                          (2010/01/05)  - Still problems with date comparison -> solved by comparing integer representation of dates
 *                                        - Missing double quotes in "histoContribByMonth" function
 *                                        - Bad behavior of "toBeCounted" function when no namespace is excluded
 *                          (2010/01/29)  - Bad behavior of "toBeCounted" function when no namespace is excluded (cont'd)
 *                                        - In "getAllChanges" function, add "htmlspecialchars" on summary storage so that summaries with single quote inside don't trigger PHP error
 * @patched by          Matthias Grimm <matthiasgrimm(at)users(dot)sourceforge(dot)net>
 *                          (2009/12/11)  - Patch correct full name not correctly displayed on HoF with non-plain auth system, now all authentication systems are supported + code cleanup
 * @patched by          Frank M.G. Joergense <frank(at)gajda(dot)dk>
 *                          (19/02/2010)  - Method to list all events - Creates, Edits, Deletes and Reverts
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

// Standard inclusions
if(!class_exists('syntax_plugin_charter')){
	if(!class_exists('pData')){
	include(DOKU_PLUGIN.'wikistatistics/pChart/pData.class');
	}
	if(!class_exists('pChart')){
	include(DOKU_PLUGIN.'wikistatistics/pChart/pChart.class');
	}
}

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_wikistatistics extends DokuWiki_Syntax_Plugin {
	var $allChanges = array();
	var $allPagesSizes = array();
	var $localExcludedns = '';
	var $localExcludedns_pattern = '';
	var $localExcludedpg_pattern = '';

	var $excludedNs = '';
	var $excludedNsPattern = '';
	var $excludedpg_pattern = '';

	var $initOpt = '';

	/**
	 * return some info
	 */
	function getInfo(){
		return array(
			'author' => 'Emanuele, Thomas',
			'email'  => 'emanuele45@interfree.it',
			'date'   => '2010-01-24',
			'name'   => 'WikiStatistics',
			'desc'   => 'Display statistics about the Wiki and their users',
			'url'	 => 'http://lacroa.altervista.org/dokucount/',
		);
	}

	/**
	 * What kind of syntax are we?
	 */
	function getType(){
		return 'substition';
	}

	/**
	 * Paragraph Type
	 *
	 * Defines how this syntax is handled regarding paragraphs. This is important
	 * for correct XHTML nesting. Should return one of the following:
	 *
	 * 'normal' - The plugin can be used inside paragraphs
	 * 'block'  - Open paragraphs need to be closed before plugin output
	 * 'stack'  - Special case. Plugin wraps other paragraphs.
	 *
	 * @see Doku_Handler_Block
	 */
	function getPType() {
		return 'normal';
	}

	/**
	 * Where to sort in?
	 */
	function getSort(){
		return 210;
	}

	/**
	 * Connect pattern to lexer
	 */
	function connectTo($mode) {
		$this->Lexer->addSpecialPattern('{{wikistatistics>.*?}}',$mode,'plugin_wikistatistics'); // <= current syntax
	}

	/**
	 * Handle the match
	 */
	function handle($match, $state, $pos, &$handler){
		//convert $match into an array of parameters

		$match = trim($match, '{}');
		$match = substr($match,strpos($match,'>')+1);

		$explodedMatch = explode(' ', $match);

		$params = array();

		for($i = 0; $i < sizeof($explodedMatch); $i++) {
			$param = trim($explodedMatch[$i]);
			$key = substr($param,0,strpos($param,'='));
			$value = substr($param,strpos($param,'=')+1);
			$params[$key] = $value;
		}

		return $params;
	}

	/**
	 * Create output
	 */
	function render($mode, &$renderer, $data) {
		if($mode == 'xhtml')
		{
			$this->varinit($data);

			switch ($data['type']) {
				case 'topcontrib': //HoF
				case 'hof':
					$renderer->doc .= $this->getTopContrib();
					break;
				case 'histocontrib':
					$renderer->doc .= $this->histoContrib();
					break;
				case 'pages':	//Total number of pages
					$renderer->doc .= $this->countPages($this->initOpt['ns']);
					break;
				case 'users':	//Total number of users
					$renderer->doc .= $this->countUsers();
					break;
				case 'pagessizes':	//
					$renderer->doc .= $this->pagesSizes();//TODO add $this->initOpt['ns']
					break;
				case 'hofpagessizes':
					$renderer->doc .= $this->getTopPagesSizes();//TODO add $this->initOpt['ns']
					break;
				case 'topedit':
					$renderer->doc .= $this->getTopChanged($this->initOpt['ns']);
					break;
				case 'lessedit':
					$renderer->doc .= $this->getTopChanged($this->initOpt['ns'],true);
					break;
			}
			unset($this->allChanges);
			return true;
		}
		return false;

	}

	/**
	 * Hall of Fame
	 */
	function getTopContrib() {
		global $auth;

		// nb of rows to display
		// if missing, 10 will be taken as default value
		$nbOfRows = (isset($this->initOpt['nbOfRows']) && is_numeric($this->initOpt['nbOfRows'])) ? $this->initOpt['nbOfRows'] : 10;

		$ret = '
	<table class="wikistat info_hof inline">
		<caption class="hof_caption">'.$this->getLang('ws_hof').'</caption>
		<tr>
			<th class="centeralign">'.$this->getLang('ws_position').'</th>
			<th class="centeralign">'.$this->getLang('ws_name').'</th>
			<th class="centeralign">'.$this->getLang('ws_editnumb').'</th>
		</tr>';

		$this->getAllChanges();

		$usersedits = array();

		// loop through all changes to count number of edits by user
		foreach($this->allChanges as $singleChange) {
			if ($singleChange['user'] != "" && $this->toBeCounted($singleChange['id'])) {
				if($singleChange['type'] != "D" && $singleChange['type'] != "R" ) {
					$usersedits[$singleChange['user']]++;
				}
			}
		}

		// use full name or pseudo ?
		$useFullName = ($this->initOpt['namecol'] == 'fullname');

		foreach($usersedits as $username => $nbofedits) {
			if($useFullName) {
				// get full user name from auth object
				$info = $auth->getUserData($username);
				$userDisplayName[$username] = (isset($info) && $info) ? hsc($info['name']) : hsc($username);
			} else {
				$userDisplayName[$username] = hsc($username);
			}
		}

		// Sort the data with volume descending, edition ascending
		// Add $data as the last parameter, to sort by the common key
		array_multisort($usersedits, SORT_DESC, $userDisplayName, SORT_ASC);

		foreach ($usersedits as $userid => $edits) {
			$evenodd = $i++ % 2 ? "hof_evenrow" : "hof_oddrow";
			if ($nbOfRows == '-1' || $i <= $nbOfRows) {//$this->getConf('ws_topcontrib')+1 || $this->getConf('ws_topcontrib') == -1) {
				$ret .= '
		<tr class="'.$evenodd.'">
			<td class="hof_row_pos"><b>'.$i.'</b></td>
			<td class="hof_row_name">'.$userDisplayName[$userid].'</td>
			<td class="hof_row_num">'.$edits.'</td>
		</tr>';
			}
		}

		$ret .= '
	</table>';

		return $ret;
	}

	function filterChanges($by='user', $onlyif=false, $andnot=array('type' => 'D', 'type' => 'R')){
		$filter = array();

		// loop through all changes to count number of edits by user
		foreach($this->allChanges as $singleChange) {
			if ($singleChange[$by] != "" && $this->toBeCounted($singleChange['id'])) {
				$add = true;
				if(is_array($onlyif)){
					foreach($onlyif as $key => $value){
						if($singleChange[$key]==$value){
							$add=true;
							break;
						}
					}
				}
				if(is_array($andnot)){
					foreach($andnot as $key => $value){
						if($singleChange[$key]==$value){
							$add=false;
							break;
						}
					}
				}
				if($add)
					$filter[$singleChange[$by]]++;
			}
		}
		return $filter;
	}

	/**
	 * Most changed pages
	 */
	function getTopChanged($ns='',$sort_asc=false) {
		global $conf;

		$this->getAllChanges('',0,0,$ns);
		$edits = $this->filterChanges('id');
		arsort($edits);
		if($sort_asc) $edits=array_reverse($edits);

		// nb of rows to display
		// if missing, 10 will be taken as default value
		$nbOfRows = (isset($this->initOpt['nbOfRows']) && is_numeric($this->initOpt['nbOfRows'])) ? $this->initOpt['nbOfRows'] : 10;

		$ret = '
	<table class="wikistat info_hof inline">
		<caption class="hof_caption">'.$this->getLang('ws_hofpagesedits') . (!empty($ns) ? $this->getLang('ws_for_ns') . $ns : '') . '</caption>
		<tr>
			<th class="centeralign">'.$this->getLang('ws_position').'</th>
			<th class="centeralign">'.$this->getLang('ws_page').'</th>
			<th class="centeralign">'.$this->getLang('ws_editnumb').'</th>
		</tr>';

		$i = 0;
		foreach($edits as $page => $pageedit) {
			$evenodd = $i++ % 2 ? 'hof_evenrow' : 'hof_oddrow';
			if ($nbOfRows == '-1' || $i <= $nbOfRows) {
				$ret .= '
		<tr class="'.$evenodd.'">
			<td class="hof_row_pos"><b>'.$i.'</b></td>
			<td class="hof_row_name">'.html_wikilink(':'.$page).'</td>
			<td class="hof_row_num">'.$pageedit.'</td>
		</tr>';
			}
		}

		$ret .= '
	</table>';

		return $ret;
	}

	/**'
	 * Edits charts
	 */
	function histoContrib() {
			switch ($this->initOpt['mode']) {
				case 'bymonth':
					return $this->histoContribByMonth();
					break;
				case 'byyear':
					break;
				case 'monthbyday':
					$period = $this->initOpt['period'];
					if($period == '') {
						$dt = getdate();
						$month = $dt['mon'];
						$year = $dt['year'];
					} else {
						$month = substr($period,0,strpos($period,"/"));
						$year = substr($period,strpos($period,"/")+1);
					}
					return $this->histoContribMonthByDay($year, $month);
					break;
				case 'lastmonthbyday':
					$dt = getdate();
					$month = $dt['mon'];
					$year = $dt['year'];
					if($month == 1) {
						$month = 12;
						$year--;
					} else {
						$month--;
					}

					return $this->histoContribMonthByDay($year, $month);
					break;
				case 'lastyear':
					break;
				case 'allevents':
					return $this->histoContribByMonthAll();
					break;
			}

	}

	/**
	 * Method to list all events - Creates, Edits, Deletes and Reverts
	 * author Frank M.G. Joergensen, frank(at)gajda(dot)dk
	 */
	function histoContribByMonthAll() {
		global $conf;
		$monthYear  = array();

		$this->getAllChanges();

		foreach($this->allChanges as $changeEvent) {
			if ($changeEvent['user'] != "" && $this->toBeCounted($changeEvent['page'])) {
				$dateConv = date("Ym", $changeEvent['date']);
				if($changeEvent['type'] == "R") $monthYear[$dateConv]['R']++;
				if($changeEvent['type'] == "D") $monthYear[$dateConv]['D']++;
				if($changeEvent['type'] == "E") $monthYear[$dateConv]['E']++;
				if($changeEvent['type'] == "C") $monthYear[$dateConv]['C']++;
			}
		}

		ksort($monthYear);
		$ret = '<table class="wikistat info_hof inline">
			<caption class="hof_caption">'.$this->getLang('ws_events').'</caption>
				<tr>
					<th class="centeralign">'.$this->getLang('ws_dateYm').'</th>
					<th class="centeralign">'.$this->getLang('ws_created').'</th>
					<th class="centeralign">'.$this->getLang('ws_edited').'</th>
					<th class="centeralign">'.$this->getLang('ws_deleted').'</th>
					<th class="centeralign">'.$this->getLang('ws_reverted').'</th>
				</tr>
				';
		if(count($monthYear)){
			$i = 0;
			foreach($monthYear as $key => $value){
				if(is_array($value)){
					$evenodd = $i++ % 2 ? 'hof_evenrow' : 'hof_oddrow';
					$ret .= '<tr class="'.$evenodd.'">
						<td class="hof_row_pos">';
					$ret .= $key;
					$ret .= '</td>
						<td class="hof_row_pos">';
					$ret .= $value['C'];
					$ret .= '</td>
						<td class="hof_row_pos">';
					$ret .= $value['E'];
					$ret .= '</td>
						<td class="hof_row_pos">';
					$ret .= $value['D'];
					$ret .= '</td>
						<td class="hof_row_pos">';
					$ret .= $value['R'];
					$ret .= '</td>
					</tr>
					';
				}
			}
			$ret .= '</table>';
		}
		return $ret;
	}

	/**'
	 * Bar graph of the number of contrib by day
	 * for the month $month of the year $year
	 */
	function histoContribMonthByDay($year, $month) {

		global $conf;

		$month = intval($month);

		$date_value = array();

		$this->getAllChanges();

		foreach($this->allChanges as $singleChange) {
			if ($singleChange['user'] != "" && $this->toBeCounted($singleChange['page'])) {
				if($singleChange['type'] != "D" && $singleChange['type'] != "R" ) {
					if(date("n/Y", $singleChange['date']) == $month."/".$year) {
						$date_value[date("j", $singleChange['date'])]++;
					}
				}
			}
		}

		// getting last day of the current month
		$monthTime = mktime(0, 0, 0, $month, 1, $year);
		$lastDayOfMonth = date("t", $monthTime);

		// Dataset definition
		$dataSet = new pData;
		$tabDays = array();
		$tabVals = array();
		for ($i = 1; $i <= $lastDayOfMonth; $i++) {
			$tabDays[$i] = $i;
			$tabVals[$i] = $date_value[$i];
		}

		$dataSet->AddPoint($tabVals, "Serie1");
		$dataSet->AddPoint($tabDays, "Serie2");
		$dataSet->AddSerie("Serie1");
		$dataSet->SetAbsciseLabelSerie("Serie2");

		// Graph width
		$width = $this->initOpt['width'];
		if($width == '') {
			$width = 700;
		}

		// Graph height
		$height = $this->initOpt['height'];
		if($height == '') {
			$height = 230;
		}

		$heightForAngle = 0;
		$absLabelAngle = $this->initOpt['absLabelAngle'];
		if($absLabelAngle > 0 && $absLabelAngle <= 90) {
			$heightForAngle = $absLabelAngle/10;
		} else {
			$absLabelAngle = 0;
		}

		// Initialize the graph
		$chart = new pChart($width,$height+$heightForAngle);
		$chart->setGraphArea($this->initOpt['spleft'],$this->initOpt['sptop'],$width-20,$height-30);

		// load colour palette from currently active template if exists.
		// Otherwise fall back to default colour palette
		if (@file_exists(DOKU_TPLINC.'palette.txt'))
			$chart->loadColorPalette(DOKU_TPLINC.'palette.txt');
		else
			$chart->loadColorPalette(DOKU_PLUGIN.'wikistatistics/palette.txt');

		$chart->setFontProperties(DOKU_PLUGIN.'wikistatistics/Fonts/tahoma.ttf',10);
		$chart->drawFilledRoundedRectangle(7,7,$width-7,$height-7+$heightForAngle,5,240,240,240);
		$chart->drawRoundedRectangle(5,5,$width-5,$height-5+$heightForAngle,5,230,230,230);
		$chart->drawGraphArea(252,252,252);
		// definition of drawScale method : drawScale($Data,$DataDescription,$ScaleMode,$R,$G,$B,$DrawTicks=TRUE,$Angle=0,$Decimals=1,$WithMargin=FALSE,$SkipLabels=1,$RightScale=FALSE)
		$chart->drawScale($dataSet->GetData(),$dataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,$absLabelAngle,2,TRUE);
		$chart->drawGrid(4,TRUE,230,230,230,255);

		// Draw the bar graph
		$chart->drawBarGraph($dataSet->GetData(),$dataSet->GetDataDescription(),TRUE);

		// Finish the graph
		$chart->setFontProperties(DOKU_PLUGIN.'wikistatistics/Fonts/tahoma.ttf',10);
		$chart->drawTitle(0,0,$this->getLang('ws_histocontribmonthbydaytitle').' '.date('F', $monthTime).' '.date('Y', $monthTime),50,50,50,$width,35);

		if (!is_dir($conf['mediadir'].'/wikistatistics'))
		{
			io_mkdir_p($conf['mediadir'].'/wikistatistics'); //Using dokuwiki framework
		}
		$chart->Render($conf['mediadir']."/wikistatistics/histocontrib_{$month}_{$year}.png");

		$url = ml("wikistatistics:histocontrib_{$month}_{$year}.png"); //Using dokuwiki framework

		$ret .= '
	<img src="' . $url . '" alt="' . $this->getLang('ws_histocontribmonthbydaytitle').' '.date('F', $monthTime).' '.date('Y', $monthTime) . '" title="' . $this->getLang('ws_histocontribmonthbydaytitle').' '.date('F', $monthTime).' '.date('Y', $monthTime) . '"/>';

		return $ret;
	}

	/**'
	 * Bar graph of the number of contrib by month
	 */
	function histoContribByMonth() {

		global $conf;

		$date_value = array();
		$dateMin = '';

		$this->getAllChanges();

		foreach($this->allChanges as $singleChange) {
			if ($singleChange['user'] != "" && $this->toBeCounted($singleChange['page'])) {
				if($singleChange['type'] != "D" && $singleChange['type'] != "R" ) {
					$dateContrib =	date("m/Y", $singleChange['date']);
					$date_value[$dateContrib]++;
					if($dateMin == '' || $singleChange['date'] < $dateMin) {
						$dateMin = $singleChange['date'];
					}
				}
			}
		}

		$dateMin = date("m/Y",$dateMin);

		// getting current month : end of the graph
		$monthNow = date("m/Y");

		// Dataset definition
		$dataSet = new pData;
		$tabMonths = array();
		$tabVals = array();

		$previousMonth = '';
		$currentMonth = $dateMin;
		$i = 0;
		$out = false;

		// since the month of the first contrib to now...
		while($previousMonth == '' || $previousMonth != $monthNow) {
			$tabMonths[$i] = $currentMonth;
			$tabVals[$i] = $date_value[$currentMonth];

			$previousMonth = $currentMonth;

			// calculate next month
			$m = substr($currentMonth,0,strpos($currentMonth,"/"));
			$y = substr($currentMonth,strpos($currentMonth,"/")+1);
			if($m == 12) {
				$m = 1;
				$y++;
			} else {
				$m++;
			}
			$currentMonth = ($m < 10 ? '0' : '').$m.'/'.$y;
			$i++;
		}

		$dataSet->AddPoint($tabVals, 'Serie1');
		$dataSet->AddPoint($tabMonths, 'Serie2');
		$dataSet->AddSerie('Serie1');
		$dataSet->SetAbsciseLabelSerie('Serie2');

		// Graph width
		$width = $this->initOpt['width'];
		if($width == '') {
			$width = 700;
		}

		// Graph height
		$height = $this->initOpt['height'];
		if($height == '') {
			$height = 230;
		}

		$heightForAngle = 0;
		$absLabelAngle = $this->initOpt['absLabelAngle'];
		if($absLabelAngle > 0 && $absLabelAngle <= 90) {
			$heightForAngle = $absLabelAngle/2;
		} else {
			$absLabelAngle = 0;
		}

		// Initialize the graph
		$chart = new pChart($width,$height+$heightForAngle);
		$chart->setGraphArea($this->initOpt['spleft'],$this->initOpt['sptop'],$width-20,$height-30);
		
		// load colour palette from currently active template if exists.
		// Otherwise fall back to default colour palette
		if (@file_exists(DOKU_TPLINC.'palette.txt'))
			$chart->loadColorPalette(DOKU_TPLINC.'palette.txt');
		else
			$chart->loadColorPalette(DOKU_PLUGIN.'wikistatistics/palette.txt');
		
		$chart->setFontProperties(DOKU_PLUGIN.'wikistatistics/Fonts/tahoma.ttf',10);
		$chart->drawFilledRoundedRectangle(7,7,$width-7,$height-7+$heightForAngle,5,240,240,240);
		$chart->drawRoundedRectangle(5,5,$width-5,$height-5+$heightForAngle,5,230,230,230);
		$chart->drawGraphArea(252,252,252);
		$chart->drawScale($dataSet->GetData(),$dataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,$absLabelAngle,2,TRUE);
		$chart->drawGrid(4,TRUE,230,230,230,255);

		// Draw the bar graph
		$chart->drawBarGraph($dataSet->GetData(),$dataSet->GetDataDescription(),TRUE);

		// Finish the graph
		$chart->setFontProperties(DOKU_PLUGIN.'wikistatistics/Fonts/tahoma.ttf',10);
		$chart->drawTitle(0,0,$this->getLang('ws_histocontribbymonthtitle'),50,50,50,$width,35);

		if (!is_dir($conf['mediadir'] . '/wikistatistics'))
		{
			io_mkdir_p($conf['mediadir'] . '/wikistatistics'); //Using dokuwiki framework
		}
		$chart->Render($conf['mediadir'].'/wikistatistics/histocontrib_bymonth.png');

		$url = ml('wikistatistics:histocontrib_bymonth.png'); //Using dokuwiki framework

		$ret .= '
	<img src="' . $url . '" alt="' . $this->getLang('ws_histocontribbymonthtitle') . '" title="' . $this->getLang('ws_histocontribbymonthtitle') . '"/>';

		return $ret;
	}

	/**
	 * Count the pages in the namespace $ns
	 */
	function countPages($ns='') {
		global $conf;

		// root directory of all pages
		$rootPath = $conf['datadir'];

		$path = '';

		// go to namespace directory if specified
		if($ns != '') {
			$nsArray = split(":",$ns);

			foreach ($nsArray as $namespace) {
				$path .= "/".$namespace;
			}
		}

		return $this->_pages_xhtml_r($path, $rootPath);
	}

	/**'
	 * Recursive method to count the number of pages under $path
	 */
	function _pages_xhtml_r($path, $rootPath) {
		$nbPages = 0;

		$nsPath = str_replace('/',':',$path);
		if($path == '' || $this->toBeCounted($nsPath)) {
			if (is_dir($rootPath.$path)) {
				if($pdir = opendir($rootPath.$path)) {
					while ($file = readdir($pdir)) {
						if ($file != "." && $file != "..") {

							$filePath = $path."/".$file;

							if (is_file($rootPath.$filePath)) {
								$filens = substr($filePath, 0, strrpos($filePath , '.txt'));

								$filens = str_replace('/',':',$filens);
								if($this->toBeCounted($filens)) {
									$nbPages++;
								}
							} else {
								$nbPages += $this->_pages_xhtml_r($filePath, $rootPath);
							}
						}
					}
					closedir($pdir);
				}
			}
		}

			return $nbPages;
		}

	/**'
	 * Check if the namespace $ns has to be excluded or not
	 */
	function toBeCounted($ns) {
		//namespace
		$ns = (preg_match("/^:/",$ns)) ? substr($ns,1) : $ns;

		$nstocheck = "";

		$ns_split = split(":",$ns);
		foreach($ns_split as $nspart){
			if($nstocheck != "") {
				$nstocheck .= ":";
			}
			$nstocheck .= $nspart;
			if (!is_null($this->excludedNs) && in_array($nstocheck,$this->excludedNs)) {
				return false;
			}
		}

		if(@preg_match($this->excludedNsPattern,$ns)){
			return false;
		}

		return true;
	}

	function cw_array_count($a) {
		if(!is_array($a)) return $a;
		foreach($a as $key=>$value)
			$totale += $this->cw_array_count($value);
		return $totale;
	}


	/**'
	 * Count the users
	 */
		function countUsers() {
		global $auth;

		$nbUsers = 0;

		if($this->initOpt['filter'] == 'active') {
			// only active users (those who contributed at least once)

			$users = array();
			$this->getAllChanges();

			foreach($this->allChanges as $singleChange) {
				$user = $singleChange['user'];
				if ($user != "" && !in_array($user, $users)) {
					$users[] = $user;
				}
			}
			$nbUsers = sizeof($users);
		} else {
			// all users

			// if the auth module implements the getUserCount function, use it !
			// it's not the case for ldap auth for example.
			if($auth->canDo('getUserCount')) {
				$nbUsers = $auth->getUserCount(array());
			}
		}

		return $nbUsers;
	}


	function getAllChanges($directory='',$first=0,$num=0,$ns='',$flags=0) {
		global $conf;

		$cache_file = $conf['mediadir'].'/wikistatistics/cache_changes' . ($ns!='' ? '_' . str_replace(':','_',$ns) : '') . '.php';

		if(!empty($ns)){
			$directory=str_replace('//','/',dirname($conf['changelog']) . '/' . str_replace(':','/',$ns));
		}

		if(!$this->getConf('ws_cacheresults')){
			@unlink($cache_file);
			$this->lastUpdate = 0;
		}
		if (!is_dir($conf['mediadir'].'/wikistatistics'))
		{
			io_mkdir_p($conf['mediadir'].'/wikistatistics'); //Using dokuwiki framework
		}
		if(@file_exists($cache_file)){
			include($cache_file);
		}

		if(time() > $this->lastUpdate + $this->getConf('ws_cacheexpire')){
			@unlink($cache_file);
			unset($this->allChanges);
			$i=0;

			$this->parseChanges($directory,$first,$num,$ns,$flags);

			$fp = @fopen( $cache_file, 'w' );
			@fwrite( $fp, '<?php
		$this->lastUpdate = ' . time() . ';');
			foreach ($this->allChanges as $change){
				@fwrite( $fp, '
		$this->allChanges['.$i.'] = array(
			\'date\' => ' . $change['date'] . ',
			\'ip\' => \'' . $change['ip'] . '\',
			\'type\' => \'' . $change['type'] . '\',
			\'id\' => \'' . $change['id'] . '\',
			\'user\' => \'' . $change['user'] . '\',
			\'sum\' => \'' . htmlspecialchars($change['sum'],ENT_QUOTES) . '\',
			\'extra\' => \'' . $change['extra'] . '\',
			);');
				$i++;
			}
			@fclose( $fp );
			include($cache_file);
		}
	}
	
	function parseChanges($directory='',$first=0,$num=0,$ns='',$flags=0) {
		global $conf;
		$metapath = $conf['metadir'];
		$count = 0;
		if(empty($directory)) {
			$directory=dirname($conf['changelog']);
		}

		//Counting files variable initialization
		$count=0;

		/*
		* open the directory and take an instance of it to handle var
		*/
		if ($handle = opendir($directory)) {
			$sub = substr($directory,strpos($directory,$metapath)+strlen($metapath)+1);
			$sub = str_replace('/',':',$sub);
			if($this->toBeCounted($sub,'ns')) {
				while (false !== ($file = readdir($handle))) {

					if ($file != "." && $file != "..") {
						if (is_file($directory."/".$file)) {
							// Determining extensions of files
							$file_ext = substr($file, strrpos($file, ".")+1);

							if($file_ext == 'changes' && $file != '_dokuwiki.changes') {
								$lines = @file("$directory/$file");
								for($i = count($lines)-1; $i >= 0; $i--) {
									$rec = parseChangelogLine($lines[$i]);
									if($rec !== false) {
										if(--$first >= 0) continue; // skip first entries
										$this->allChanges[] = $rec;
										$count++;
										// break when we have enough entries
										if(!$num==0) {
											if($count >= $num) {
												break;
											}
										}
									}
								}
							}
						} else if (is_dir("$directory/$file")) {
							$this->parseChanges("$directory/$file");
						}
					}
				}
			}

			closedir($handle);
		}
	}

	function pagesSizes($ns = '') {
		global $conf;

		// root directory of all pages
		$rootPath = $conf['datadir'];

		$path = '';

		// go to namespace directory if specified
		if($ns != '') {
			$nsArray = split(':',$ns);

			foreach ($nsArray as $namespace) {
				$path .= '/'.$namespace;
			}
		}

		// Max level
		$depthLevel = $this->initOpt['depthlevel'];
		if($depthLevel == '') {
			$depthLevel = 0;
		}

		$pagesSizes = $this->getAllPagesSizes($path, $rootPath, $depthLevel);

		$nss = array();
		$sizes = array();
		foreach($pagesSizes as $ns => $size) {
			$nss[] = $ns." (".$size.")";
			$sizes[] = $size;
		}

		// Graph width
		$width = $this->initOpt['width'];
		if($width == '') {
			$width = 530;
		}

		// Graph height
		$height = $this->initOpt['height'];
		if($height == '') {
			$height = 200;
		}

		$legendX = $width-min(220, round($width/3));
		$graphX = round($legendX/2);
		$graphY = round($height/2);
		$graphR = round(($graphX<$graphY)?$graphX:min($graphX,$graphY*1.6))-50;

		// Dataset definition
		$DataSet = new pData;
		$DataSet->AddPoint($sizes,'sizes');
		$DataSet->AddPoint($nss,'namespaces');
		$DataSet->AddAllSeries();
		$DataSet->SetAbsciseLabelSerie('namespaces');

		// Initialise the graph
		$chart = new pChart($width,$height);
		$chart->drawFilledRoundedRectangle(7,7,$width-7,$height-7,5,240,240,240);
		$chart->drawRoundedRectangle(5,5,$width-5,$height-5,5,230,230,230);

		// load colour palette from currently active template if exists.
		// Otherwise fall back to default colour palette
		if (@file_exists(DOKU_TPLINC.'palette.txt'))
			$chart->loadColorPalette(DOKU_TPLINC.'palette.txt');
		else
			$chart->loadColorPalette(DOKU_PLUGIN.'wikistatistics/palette.txt');

		// Draw the pie chart
		$chart->setFontProperties(DOKU_PLUGIN.'wikistatistics/Fonts/tahoma.ttf',10);
		// drawPieGraph($Data,$DataDescription,$XPos,$YPos,$Radius=100,$DrawLabels=PIE_NOLABEL,$EnhanceColors=TRUE,$Skew=60,$SpliceHeight=20,$SpliceDistance=0,$Decimals=0)
		$chart->drawPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),$graphX,$graphY,$graphR,PIE_PERCENTAGE,TRUE,50,20,5);
		$chart->drawPieLegend($legendX,15,$DataSet->GetData(),$DataSet->GetDataDescription(),250,250,250);

		$chart->Render($conf['mediadir'].'/wikistatistics/pagessizes.png');

		$url = ml('wikistatistics:pagessizes.png'); //Using dokuwiki framework

		$ret = '
	<img src="' . $url . '" alt="' . $this->getLang('ws_pagesize') . '" title="' . $this->getLang('ws_pagesize') . '"/>';

		return $ret;

	}

	/**
	 * Calculate the size of all pages
	 */
	function getAllPagesSizes($path, $rootPath, $depthLevel) {
		$pagesSizes = array();

		$nsPath = str_replace('/',':',$path);
		if($path == '' || $this->toBeCounted($nsPath)) {
			if (is_dir($rootPath.$path)) {
				if($pdir = opendir($rootPath.$path)) {
					while ($file = readdir($pdir)) {
						if ($file != '.' && $file != '..') {

							$filePath = "$path/$file";

							if (is_file($rootPath.$filePath)) {
								$filens = substr($filePath, 0, strrpos($filePath , '.txt'));

								$filens = str_replace('/',':',$filens);
								if(substr($filens, 0, 1) == ':') {
									$filens = substr($filens, 1);
								}
								if($this->toBeCounted($filens)) {
									if($depthLevel > 0) {
										$nsArray = split(':', $filens);
										$filens = '';
										for($i=0; $i<sizeof($nsArray) && $i<$depthLevel; $i++) {
											if($i > 0) {
												$filens .= ':';
											}
											$filens .= $nsArray[$i];

										}
									}
									$pagesSizes[$filens] = filesize($rootPath.$filePath);
								}
							} else {
								foreach($this->getAllPagesSizes($filePath, $rootPath, $depthLevel) as $ns => $size) {
									$pagesSizes[$ns] += $size;
								}

							}
						}
					}
					closedir($pdir);
				}
			}
		}

		return $pagesSizes;
	}

	function getTopPagesSizes($path = '') {
		global $conf;

		// root directory of all pages
		$rootPath = $conf['datadir'];

		// nb of rows to display
		// if missing, 10 will be taken as default value
		$nbOfRows = (isset($this->initOpt['nbOfRows']) && is_numeric($this->initOpt['nbOfRows'])) ? $this->initOpt['nbOfRows'] : 10;

		$pagesSizes = $this->getAllPagesSizes($path, $rootPath, 0);

		arsort($pagesSizes);

		$ret = '
	<table class="wikistat info_hof inline">
		<caption class="hof_caption">'.$this->getLang('ws_hofpagessizes').'</caption>
		<tr>
			<th class="centeralign">'.$this->getLang('ws_position').'</th>
			<th class="centeralign">'.$this->getLang('ws_page').'</th>
			<th class="centeralign">'.$this->getLang('ws_size').'</th>
		</tr>';

		$i = 0;
		foreach($pagesSizes as $page => $pagesize) {
			$evenodd = $i++ % 2 ? 'hof_evenrow' : 'hof_oddrow';
			if ($nbOfRows == '-1' || $i <= $nbOfRows) {
				$ret .= '
		<tr class="'.$evenodd.'">
			<td class="hof_row_pos"><b>'.$i.'</b></td>
			<td class="hof_row_name">'.html_wikilink(':'.$page).'</td>
			<td class="hof_row_num">'.$pagesize.'</td>
		</tr>';
			}
		}

		$ret .= '
	</table>';

		return $ret;
	}

	/*
	*
	*	Variable's initialization
	*
	*/
	function varinit($params) {
		$this->allChanges = array(); //Reset the counter

		$this->initOpt=$params;

		// param ws_excludedns
		$excludedNs = split(",",$this->getConf('ws_excludedns'));
		foreach ($excludedNs as $key => $value) {
			if (is_null($value) || $value=="") {
				unset($excludedNs[$key]);
			}
		}

		$this->excludedNs = (count($excludedNs)>0) ? $excludedNs : null;
		// param ws_excludedns_pattern
		$excludedns_pattern = split(",",$this->getConf('ws_excludedns_pattern'));
		$this->excludedNsPattern = '/';
		for($i=0;$i<count($excludedns_pattern);$i++){
			if($excludedns_pattern[$i]!='')
				$this->excludedNsPattern .= $excludedns_pattern[$i].'|';
		}
		$this->excludedNsPattern = substr($this->excludedNsPattern, 0, -1).'/';
		if($this->excludedNsPattern=='/'){$this->excludedNsPattern='';}

		$this->initOpt['spleft'] = isset($this->initOpt['spleft']) ? $this->initOpt['spleft'] : '40';
		$this->initOpt['sptop'] = isset($this->initOpt['sptop']) ? $this->initOpt['sptop'] : '30';
	}
}