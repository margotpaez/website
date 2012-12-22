<?php
/********************************************************************************************************************************
*
* LabVIEW Hacker Landing Page Plugin
*
/*******************************************************************************************************************************/
 
// must be run within DokuWiki
if(!defined('DOKU_INC')) die();
 
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once DOKU_PLUGIN.'syntax.php';
 
//Include LVH Plugin Common Code
if(!defined('LVH_COMMON'))
{
	define('LVH_COMMON', '/var/www/wordpress/wiki2/lib/plugins/lvhplugincommon.php');
	include '/var/www/wordpress/wiki2/lib/plugins/lvhplugincommon.php'; 
}
 
/********************************************************************************************************************************
* All DokuWiki plugins to extend the parser/rendering mechanism
* need to inherit from this class
********************************************************************************************************************************/
class syntax_plugin_lvhlandingpage extends DokuWiki_Syntax_Plugin 
{
	//Return Plugin Info
	function getInfo() 
	{
        return array('author' => 'Sammy_K',
                     'email'  => 'sammyk.labviewhacker@gmail.com',
                     'date'   => '2012-12-21',
                     'name'   => 'LabVIEW Hacker Landing Page Plugin',
                     'desc'   => 'Template for LabVIEW Hacker Landing Pages',
                     'url'    => 'www.labviewhacker.com');
    }

	
	//include 'common.php';	
	//protected   $imageFetchPath = 'http://75.101.137.8/wiki2/lib/exe/fetch.php?media=';

	//Set This To True To Enable Debug Strings
	protected $lvhDebug = false;
	
	/***************************************************************************************************************************
	* Plugin Variables
	***************************************************************************************************************************/
	protected	$fullName = '';
	protected	$shortName = '';
	protected	$description = '';
	protected	$logoPath = '';
	protected	$gettingStartedPath = '';
	protected	$tutorialsPath = '';
	protected	$forumPath = '';
	protected	$gitHubPath = '';
	protected	$howItWorks = '';
	protected   $howItWorksPath = '';
	protected	$exploreFeatures = '';
	protected   $exploreFeaturesPath = '';
	protected	$seeItInAction = '';
	protected   $seeItInActionPath = '';
	protected	$gettingStarted = '';		
  
    function getType() { return 'protected'; }
    function getSort() { return 32; }
  
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('{{lvh_landingpage.*?(?=.*?}})',$mode,'plugin_lvhlandingpage');
		
		//Add Internal Pattern Match For Product Page Elements	
		$this->Lexer->addPattern('\|.*?(?=.*?)\n','plugin_lvhlandingpage');
    }
	
    function postConnect() {
      $this->Lexer->addExitPattern('}}','plugin_lvhlandingpage');
    }
	 
    function handle($match, $state, $pos, &$handler) 
	{	
		global $imageFetchPath;
		
		switch ($state) 
		{
		
			case DOKU_LEXER_ENTER :
				break;
			case DOKU_LEXER_MATCHED :					
				//Find The Token And Value (Before '=' remove white space, convert to lower case).
				$tokenDiv = strpos($match, '=');								//Find Token Value Divider ('=')
				$token = strtolower(trim(substr($match, 1, ($tokenDiv - 1))));	//Everything Before '=', Remove White Space, Convert To Lower Case
				$value = substr($match, ($tokenDiv + 1));						//Everything after '='
				switch($token)
				{
					case 'full name':						
						$this->fullName = $value;
						break;	
					case 'short name':						
						$this->shortName = $value;
						break;	
					case 'getting started path':						
						$this->gettingStartedPath = $value;
						break;	
					case 'github path':						
						$this->gitHubPath = $value;
						break;
					case 'tutorials path':						
						$this->tutorialsPath = $value;
						break;
					case 'forum path':						
						$this->forumPath = $value;
						break;	
					case 'description':						
						$this->description = $value;
						break;				
					case 'logo path':						
						$this->logoPath = lvh_getImageURL($value);
						break;
					case 'how it works':						
						$this->howItWorks = $value;
						break;	
					case 'how it works path':						
						$this->howItWorksPath = $value;
						break;
					case 'explore features':						
						$this->exploreFeatures = $value;
						break;	
					case 'explore features path':						
						$this->exploreFeaturesPath = $value;
						break;	
					case 'see it in action':						
						$this->seeItInAction = $value;
						break;	
					case 'see it in action path':						
						$this->seeItInActionPath = $value;
						break;	
					case 'getting started':						
						$this->gettingStarted = $value;
						break;							
					default:
						break;
				}
				break;
			case DOKU_LEXER_UNMATCHED :
				break;
			case DOKU_LEXER_EXIT :
				return array($state, $this->fullName, $this->shortName, $this->description, $this->logoPath, $this->gettingStartedPath, $this->tutorialsPath, $this->forumPath, $this->gitHubPath, $this->howItWorks, $this->howItWorksPath, $this->exploreFeatures, $this->exploreFeaturesPath, $this->seeItInAction, $this->seeItInActionPath, $this->gettingStarted);
				break;
			case DOKU_LEXER_SPECIAL :
				break;
		}
			
		return array($state, $match);
    }
 
    function render($mode, &$renderer, $data) 
	{
    // $data is what the function handle return'ed.
        if($mode == 'xhtml')
		{		
			
			//$renderer->doc .= $this->fullName;
			switch ($data[0]) 
			{
			  case DOKU_LEXER_ENTER : 
				//Initialize Table	
				if($this->skDebug) $renderer->doc .= 'ENTER';		//Debug
				
				//$renderer->doc.= '<HTML><body><table border="0">';
				break;
			  case DOKU_LEXER_MATCHED :
				//Add Table Elements Based On Type
				if($this->skDebug) $renderer->doc .= 'MATCHED';		//Debug
								
				//$renderer->doc .= '<tr><td>';
				//$renderer->doc .= $data[2];	
				//$renderer->doc .= '</td></tr>';
				
				break;
			  case DOKU_LEXER_UNMATCHED :
				//Ignore
				if($this->skDebug) $renderer->doc .= 'UNMATCHED';	//Debug
				break;
			  case DOKU_LEXER_EXIT :
				//Close Elements
				if($this->skDebug) $renderer->doc .= 'EXIT';		//Debug
				
				//Break Out Local Variables For Rendering
				$instfullName = $data[1];
				$instshortName = $data[2];
				$instdescription = $data[3];
				$instlogoPath = $data[4];
				$instgettingStartedPath = $data[5];				
				$insttutorialsPath = $data[6];
				$instforumPath = $data[7];
				$instgitHubPath = $data[8];
				$insthowItWorks = $data[9];
				$insthowItWorksPath = $data[10];
				$instexploreFeatures = $data[11];
				$instexploreFeaturesPath = $data[12];
				$instseeItInAction = $data[13];
				$instseeItInActionPath = $data[14];
				$instgettingStarted = $data[15];		
				
				$renderer->doc .= "
					<HTML>
						<head>
							<style type='text/css'>
								.productPage { border-collapse: collapse; width:100%; background-color:white;}
								.productPage-head { border:0;margin-bottom:0;padding-bottom:0; }
								.productPage-head td { border:0; }
								.productPage-body { border:0;border-top:0;margin-top:0;padding-top:0;margin-bottom:0;padding-bottom:0; width:100%; }
								.productPage-body td { border:0;border-top:0;}
								.productPage-footer { border:0;border-top:0;margin-top:0;padding-top:0; }
								.productPage-footer td { border:0;border-top:0;}
							</style>
						</head>
						<body>
							<table class='productPage productPage-head'>
								<tr>
									<td>
										<h1> " . $instfullName . " </h1>	
									</td>  					
								</tr>								
							</table>
							
							<table class='productPage productPage-body'>
								<tr>
									<td colspan='4'>
										" . $instdescription . " <br />
									</td>
									<td rowspan=\"2\">
										<p align='center'><img src='" . $instlogoPath . "' width='60%' height='60%'> </p>
									</td>	
								</tr>
								<tr>
									<td width='15%'>
										<p align='center'><a href='doku.php?id=" . $instgettingStartedPath . "'><img src=\"/wiki2/lib/exe/fetch.php?media=libraries:getting_started_black.png\" onmouseover=\"this.src='/wiki2/lib/exe/fetch.php?media=libraries:getting_started_green.png'\" onmouseout=\"this.src='/wiki2/lib/exe/fetch.php?media=libraries:getting_started_black.png'\" /><br />Getting Started </a><br /></p>
									</td>
									<td width='15%'>
										<p align='center'><a href='doku.php?id=" . $insttutorialsPath . "'><img src=\"/wiki2/lib/exe/fetch.php?media=libraries:tutorials_black.png\" onmouseover=\"this.src='/wiki2/lib/exe/fetch.php?media=libraries:tutorials_green.png'\" onmouseout=\"this.src='/wiki2/lib/exe/fetch.php?media=libraries:tutorials_black.png'\" /><br />Tutorials </a><br /></p>
									</td>
									<td width='15%'>
										<p align='center'><a href='" . $instforumPath . "'><img src=\"/wiki2/lib/exe/fetch.php?media=libraries:forums_black.png\" onmouseover=\"this.src='/wiki2/lib/exe/fetch.php?media=libraries:forums_green.png'\" onmouseout=\"this.src='/wiki2/lib/exe/fetch.php?media=libraries:forums_black.png'\" /><br />Forums </a><br /></p>
									</td>
									<td width='15%'>
										<p align='center'><a href='" . $instgitHubPath . "'><img src=\"/wiki2/lib/exe/fetch.php?media=libraries:github_black.png\" onmouseover=\"this.src='/wiki2/lib/exe/fetch.php?media=libraries:github_green.png'\" onmouseout=\"this.src='/wiki2/lib/exe/fetch.php?media=libraries:github_black.png'\" /><br />Git Hub </a><br /></p>
									</td>
																							
								</tr>
							</table>
							
							
							<table class='productPage productPage-footer'>
								<tr>
									<td width='25%' style=\"border-right: dotted 2px #CCCCCC; padding-left: 20px; padding-right: 20px;\">
										<p><b>How It Works</b><br /><br />
										" . $insthowItWorks . "</p>										
									</td>
									<td width='25%' style=\"border-right: dotted 2px #CCCCCC; padding-left: 20px; padding-right: 20px;\">
										<p><b>Explore Features</b><br /><br />
										" . $instexploreFeatures . "</p>																				
									</td>
									<td width='25%' style=\"border-right: dotted 2px #CCCCCC; padding-left: 20px; padding-right: 20px;\">
										<p><b>See It In Action</b><br /><br />
										" . $instseeItInAction . "</p>	
									</td>
									<td width='25%' style=\"padding-left: 20px; padding-right: 20px;\">
										<p><b>Getting Started</b><br /><br />
										" . $instgettingStarted . "</p>												
									</td>
								</tr>
								<tr>
									<td style=\"border-right: dotted 2px #CCCCCC; padding-left: 20px; \">
										<a href='doku.php?id=" . $insthowItWorksPath . "'> See How " . $instshortName . " Works</a>
									</td>
									<td style=\"border-right: dotted 2px #CCCCCC; padding-left: 20px; \">
										<a href='doku.php?id=" . $instexploreFeaturesPath . "'> Explore " . $instshortName . " Features</a> 	
									</td>
									<td style=\"border-right: dotted 2px #CCCCCC; padding-left: 20px; \">
										<a href='doku.php?id=" . $instseeItInActionPath . "'> Projects Using " . $instshortName . "</a> 
									</td>
									<td style=\"padding-left: 20px;\">
										 <a href='doku.php?id=" . $instgettingStartedPath . "'> Start Using " . $instshortName . "</a> 
									</td>
							</table>
						</body>
					</HTML>				
					";
				
				
				
				break;
			  case DOKU_LEXER_SPECIAL :
				//Ignore
				if($this->skDebug) $renderer->doc .= 'SPECIAL';		//Debug
				break;
			}			
            return true;
        }
        return false;
    }
}

?>