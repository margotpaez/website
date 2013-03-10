<?php
/**
 * Plugin SK Product Page: Dreamgineer Product Page.
 *
 */
 
// must be run within DokuWiki
if(!defined('DOKU_INC')) die();
 
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once DOKU_PLUGIN.'syntax.php';
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_skproductpage extends DokuWiki_Syntax_Plugin 
{

	//Set This To True To Enable Debug Strings
	protected $skDebug = false;
	
	//Store Variables To Render
	protected	$fullName = '';
	protected	$shortName = '';
	protected	$description = '';
	protected	$logoPath = '';
	protected	$gettingStartedPath = '';
	protected	$gitHubPath = '';
	protected	$tutorialsPath = '';
	protected	$faqPath = '';
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
        $this->Lexer->addEntryPattern('{{skproductpage.*?(?=.*?}})',$mode,'plugin_skproductpage');
		
		//Add Internal Pattern Match For Product Page Elements	
		$this->Lexer->addPattern('\|.*?(?=.*?)\n','plugin_skproductpage');
    }
	
    function postConnect() {
      $this->Lexer->addExitPattern('}}','plugin_skproductpage');
    }
	 
    function handle($match, $state, $pos, &$handler) 
	{	
		
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
					case 'faq path':						
						$this->faqPath = $value;
						break;	
					case 'description':						
						$this->description = $value;
						break;				
					case 'logo path':						
						$this->logoPath = $value;
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
				return array($state, $value);
				break;
			case DOKU_LEXER_UNMATCHED :
				break;
			case DOKU_LEXER_EXIT :
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
				//$renderer->doc.= '</table></body></HTML>';
				
				$renderer->doc .= "
					<HTML>
						<head>
							<style type='text/css'>
								.productPage { border-collapse: collapse; width:100%; background-color:white;}
								.productPage-head { border:0;margin-bottom:0;padding-bottom:0; }
								.productPage-head td { border:0; }
								.productPage-body { border:0;border-top:0;margin-top:0;padding-top:0;margin-bottom:0;padding-bottom:0; }
								.productPage-body td { border:0;border-top:0;}
								.productPage-footer { border:0;border-top:0;margin-top:0;padding-top:0; }
								.productPage-footer td { border:0;border-top:0;}
							</style>
						</head>
						<body>
							<table class='productPage productPage-head'>
								<tr>
									<td>
										<h1> " . $this->fullName . " </h1>	
									</td>  						
									<td width='10%'>
										<p align='center'><a href='doku.php?id=" . $this->gettingStartedPath . "'>Getting Started </a></p>
									</td>
									<td width='10%'>          
										<p align='center'><a href=" . $this->gitHubPath . ">GitHub</a></p>
									</td>
									<td width='10%'>          
										<p align='center'><a href='doku.php?id=" . $this->tutorialsPath . "'>Tutorials</a></p>
									</td>
									<td width='10%'>          
										<p align='center'><a href='doku.php?id=" . $this->faqPath . "'>FAQ</a></p>
									</td> 								
								</tr>
								<tr>
									<td width='50%'>
										<p>	" . $this->description . "</p>							
									</td>
									<td width='50%' colspan='4'>
										<p align='center'><img src='" . $this->logoPath . "' width='60%' height='60%'> </p>
									</td>
								</tr>
							</table>
							<table class='productPage productPage-body'>
								<tr>
									<td>
										<p><b>How It Works</b><br /><br />
										" . $this->howItWorks . "</p>
										<a href='doku.php?id=" . $this->howItWorksPath . "'> See How " . $this->shortName . " Works</a> 
									</td>
									<td>
										<p><b>Explore Features</b><br /><br />
										" . $this->exploreFeatures . "</p>	
										<a href='doku.php?id=" . $this->exploreFeaturesPath . "'> Explore " . $this->shortName . " Features</a> 										
									</td>
									<td>
										<p><b>See It In Action</b><br /><br />
										" . $this->seeItInAction . "</p>	
											<a href='doku.php?id=" . $this->seeItInActionPath . "'> Projects Using " . $this->shortName . "</a> 
									</td>
									<td>
										<p><b>Getting Started</b><br /><br />
										" . $this->gettingStarted . "</p>	
											<a href='doku.php?id=" . $this->gettingStartedPath . "'> Start Using " . $this->shortName . "</a> 
									</td>
								</tr>
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
	