<?php
/**
 * Plugin opstutorialtile.
 *
 */
 
// must be run within DokuWiki
if(!defined('DOKU_INC')) die();
 
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once DOKU_PLUGIN.'syntax.php';

//Include OPS Plugin Common Code
if(!defined('OPS_COMMON'))
{
	define('OPS_COMMON', '/var/www/wordpress/wiki2/lib/plugins/ops_plugin_common.php');
	include '/var/www/wordpress/wiki2/lib/plugins/ops_plugin_common.php'; 
}
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_opstutorialtile extends DokuWiki_Syntax_Plugin 
{

	//Set This To True To Enable Debug Strings
	protected $skDebug = false;
	
	//Quick Customizations
	protected $maxImageSize = 200;
	
	//Store Variables To Render
	protected $title = '';	
	protected $image = '';
	protected $description = '';
	protected $date = '';
	protected $hacker = '';
	
  /********************************************************************************************************************************************
	** Plugin Configuration
	********************************************************************************************************************************************/			
				
    function getType() { return 'container'; }
    function getSort() { return 2; }
  
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('{{tutorial_tile.*?(?=.*?}})',$mode,'plugin_opstutorialtile');
		
		//Add Internal Pattern Match For Product Page Elements	
		$this->Lexer->addPattern('\|.*?(?=.*?)\n','plugin_opstutorialtile');
    }
	
    function postConnect() {
      $this->Lexer->addExitPattern('}}','plugin_opstutorialtile');
    }
	 
	/********************************************************************************************************************************************
	** Handle
	********************************************************************************************************************************************/			
				
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
					case 'title':						
						$this->title = ops_parseWikiSyntax($value);
						break;						
					case 'image':						
						$this->image = ops_getImageLink($value);
						break;
					case 'description':						
						$this->description = $value;
						break;	
					case 'date':						
						$this->date = $value;
						break;
					case 'hacker':						
						$this->hacker = $value;
						break;						
					default:
						break;
				}
				return array($state, $value);
				break;
			case DOKU_LEXER_UNMATCHED :
				break;
			case DOKU_LEXER_EXIT :
				$retVal = array($state, $this->title, $this->image, $this->description, $this->date, $this->hacker);
					//Clear Variables Thta Will Be Resused Here If Neccissary (might not be needed in this plugin)
				return $retVal;
				break;
			case DOKU_LEXER_SPECIAL :
				break;
		}			
		return array($state, $match);
    }
 
	/********************************************************************************************************************************************
	** Render
	********************************************************************************************************************************************/
	
    function render($mode, &$renderer, $data) 
	{
    // $data is what the function handle return'ed.
        if($mode == 'xhtml')
		{
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
				break;
			  case DOKU_LEXER_UNMATCHED :
				//Ignore
				if($this->skDebug) $renderer->doc .= 'UNMATCHED';	//Debug
				break;
			  case DOKU_LEXER_EXIT :
				//Close Elements
				if($this->skDebug) $renderer->doc .= 'EXIT';		//Debug
				//$renderer->doc.= '</table></body></HTML>';
				
				//Separate Data
				 $instTitle = $data[1];
				 $instImage = $data[2];
				 $instDescription = $data[3];
				 $instDate = $data[4];
				 $instHacker = $data[5];
				
				$renderer->doc .= "
					<head>
						<style type='text/css'>
						
							table.libraryTile
							{  
								width:30%;
								border:2px solid;
								border-color:#CCCCCC;
								background-color: white;	
								float:left;
								margin:10px;
							}
							
							tr.libraryTileRow
							{ 
								border:0px;	
							}							

							td.libraryTileCell
							{ 
								border:0px;
								vertical-align:middle;	
							}	

							
							
						</style>
					</head>

					<body>
						<table class='libraryTile'>
							<tr>
								<td class='libraryTileCell'>
									<font size='4em'>" . $instTitle . "</font>
								</td>
							</tr>
							<tr>
								<td class='libraryTileCell'>
									<font size='2em'>Hacked By: <b>" . $instHacker . "</b><br />Date:" . $instDate . " </align></font>
								</td>
							</tr>
							<tr>
								<td class='libraryTileCell'>
									<center>" . $instImage . "</center>
								</td>
							</tr>
							<tr>
								<td class='libraryTileCell'>
									" . $instDescription . " Read more about " . $instTitle . "
								</td>
							</tr>
						</table>
					</body>				
				";		
				
				$ret = p_render('xhtml',p_get_instructions($instDescription),$info);
				$renderer->doc .= $ret;
				
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
	