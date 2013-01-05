<?php
/********************************************************************************************************************************
*
* LabVIEW Hacker Infobox Component Plugin
*
* Written By Sammy_K
* www.labviewhacker.com
*
/*******************************************************************************************************************************/
  
// must be run within DokuWiki
if(!defined('DOKU_INC')) die();
 
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once DOKU_PLUGIN.'syntax.php';

//Include LVH Plugin Common Code
if(!defined('LVH_COMMON'))
{
	define('LVH_COMMON', 'lib/plugins/lvhplugincommon.php');
	include 'lib/plugins/lvhplugincommon.php'; 
}


 
/********************************************************************************************************************************
* All DokuWiki plugins to extend the parser/rendering mechanism
* need to inherit from this class
********************************************************************************************************************************/
class syntax_plugin_lvhinfoboxcomponent extends DokuWiki_Syntax_Plugin 
{
	
	
	//Return Plugin Info
	function getInfo() 
	{
        return array('author' => 'Sammy_K',
                     'email'  => 'sammyk.labviewhacker@gmail.com',
                     'date'   => '2012-12-21',
                     'name'   => 'LabVIEW Hacker Infobox Component Plugin',
                     'desc'   => 'LabVIEW Hacker Infobox Component Plugin',
                     'url'    => 'www.labviewhacker.com');
    }

	//Set This To True To Enable Debug Strings
	protected $lvhDebug = false;
	
	//Store Variables To Render	
	//Basics
	protected $name = '';	
	protected $category = '';		
	protected $image = '';

	//Product History
	protected $manufacturer = '';
	
	//General Specs
	protected $width = '';
	protected $depth = '';
	protected $height = '';
	protected $pins = '';
		
	//Electrical
	
	
	/********************************************************************************************************************************************
	** Plugin Configuration
	********************************************************************************************************************************************/			
				
    function getType() { return 'protected'; }
    function getSort() { return 32; }
  
    function connectTo($mode) 
	{
        $this->Lexer->addEntryPattern('{{lvh_infobox_component.*?(?=.*?}})',$mode,'plugin_lvhinfoboxcomponent');
		
		//Add Internal Pattern Match For Product Page Elements	
		$this->Lexer->addPattern('\|.*?(?=.*?)\n','plugin_lvhinfoboxcomponent');
    }
	
    function postConnect() 
	{
      $this->Lexer->addExitPattern('}}','plugin_lvhinfoboxcomponent');
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
					//Basics
					case 'name':						
						$this->name = $value;
						break;	
					case 'category':
						$this->category = lvh_allowSimpleWikiSyntax($value);
						break;
					case 'image':						
						$this->image = lvh_getImageLink($value);
						break;
						
					//Product History
					case 'manufacturer':						
						$this->manufacturer = lvh_allowSimpleWikiSyntax($value);
						break;

					//General Specs
					case 'width':						
						$this->width = $value;
						break;
					case 'depth':						
						$this->depth = $value;
						break;
					case 'height':						
						$this->height = $value;
						break;
					case 'pins':						
						$this->pins = $value;
						break;
						
					//Default
					default:
						break;
				}
				return array($state, $value);
				break;
			case DOKU_LEXER_UNMATCHED :
				break;
			case DOKU_LEXER_EXIT :
			
				$basics = parseBasics($this->name, $this->category, $this->image);
				$productHistory = parseProductHistory($this->manufacturer);
				$generalSpecs = parseGeneralSpecs($this->width, $this->depth, $this->height, $this->pins);
				$retVal = array($state, $basics, $productHistory, $generalSpecs);
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
				if($this->lvhDebug) $renderer->doc .= 'ENTER';		//Debug
				
				//$renderer->doc.= '<HTML><body><table border="0">';
				break;
			  case DOKU_LEXER_MATCHED :
				//Add Table Elements Based On Type
				if($this->lvhDebug) $renderer->doc .= 'MATCHED';		//Debug				
				break;
			  case DOKU_LEXER_UNMATCHED :
				//Ignore
				if($this->lvhDebug) $renderer->doc .= 'UNMATCHED';	//Debug
				break;
			  case DOKU_LEXER_EXIT :
				//Close Elements
				if($this->lvhDebug) $renderer->doc .= 'EXIT';		//Debug
				//$renderer->doc.= '</table></body></HTML>';
				
				//Separate Data
				 $instBasics = $data[1];	
				 $instProductHistory = $data[2];
				 $instGeneralSpecs = $data[3];
				
				$renderer->doc .= "
					<head>
						<style type='text/css'>
						
							table.infoboxComponentOuterTable
							{  
								float:right;
								margin:10px;								
								width:30%;
								
								border: 0px solid #BBBBBB;
								border-collapse:collapse;

								background-color: #EEEEEE;								
								background-color: #EEEEEE;								
							}
							
							.infoboxComponentName 
							{ 		
								border-top: 0px;
								border-left: 0px solid #BBBBBB;
								border-right: 0px solid #BBBBBB;
								border-bottom: 1px solid #BBBBBB;
								background-color: white;
								
								font-weight:bold;
								font-size:1em;
							}
							
							table.infoboxComponentInnerTable
							{  
								width:100%;
								border:0px solid;
								border-color:#BBBBBB;
								background-color: #EEEEEE;	
							}
							
							.infoboxComponentImage
							{ 	
								border:0px solid;
								padding:10px;
							} 
							
							.infoboxComponentSectionHeader
							{ 
								vertical-align:middle;
								background-color: #BBBBBB;	
								padding:0px;
								
								font-size:.85em;
								font-weight:bold;
							}
							
							.infoboxComponentLabel
							{ 
								width:35%;
								
								border:0px;								
								vertical-align:middle;
								padding:2px;
								
								font-size:.75em;
								font-weight:bold;
							}
							
							.infoboxComponentValue
							{ 
								border:0px;
								vertical-align:middle;
								padding:2px;
								
								font-size:.75em;
							}
						</style>
					</head>

					<body>
						" . $instBasics 				
						  . $instProductHistory
						  . $instGeneralSpecs . "
						  
									</table>
								</td>
							</tr>
						</table>
					</body>				
				";		
				
				break;
			  case DOKU_LEXER_SPECIAL :
				//Ignore
				if($this->lvhDebug) $renderer->doc .= 'SPECIAL';		//Debug
				break;
			}			
            return true;
        }
        return false;
    }
}

function parseBasics($name, $category, $image)
{
	$retVal = "<table class='infoboxComponentOuterTable'>";
	
	$name = trim($name);
	$category = trim($category);
	$image = trim($image);
	
	if( ($name == '') && ($category == '') && ($image == '') )
	{
		//This Section Contains No Data - Nothing To Render
		return '';
	}
	else
	{
		//This Section Contains Data.  Add Each Element With Data
		//Add Name And Open Inner Table (Infoboxes Should Always Have A Name)
		if($name != '')
		{
			$retVal .=	"<tr>
							<td class='infoboxComponentName' colspan='2'>
								<center>" . $name . "</center>
							</td>
						</tr>
						<tr>
							<td>
							<table class='infoboxComponentInnerTable'>";
		}
		//Add Image
		if($image != '')
		{
			$retVal .= "<tr>
							<td class='infoboxComponentImage' colspan='2'>
								<center>" . $image . "</center>
							</td>
						</tr>";
		}
		//Add Category
		if($category != '')
		{
			$retVal .= "<tr>
							<td class='infoboxComponentLabel'>
								Category
							</td>
							<td class='infoboxComponentValue'>
								" . $category . "
							</td>
						</tr>";
		}
		
		return $retVal;
	}
}

function parseProductHistory($manufacturer)
{
	$retVal = '';
	
	$manufacturer = trim($manufacturer);
	
	if($manufacturer == '')
	{
		//This Section Contains No Data - Nothing To Render
		return $retVal;
	}
	else
	{
		//Section Contains Data.  Add Section Header
		$retVal .= "<tr>
						<td class='infoboxComponentSectionHeader' colspan='2'>
							<center>Product History</center>
						</td>
					</tr>";
		
		//Add Section Labels and Values
		
		//Add Manufacturer Label / Value (If It Exists)
		if($manufacturer != '')
		{
			$retVal .= "<tr>
							<td class='infoboxComponentLabel'>
								Manufacturer
							</td>
							<td class='infoboxComponentValue'>
								" . $manufacturer . "
							</td>
						</tr>";
		}
		
		return $retVal;
	}
}

function parseGeneralSpecs($width, $depth, $height, $pins)
{
	$retVal = '';
	
	$width = trim($width);
	$depth = trim($depth);
	$height = trim($height);
	$pins = trim($pins);
	
	if( ($width == '') && ($height == '') && ($pins == '') )
	{
		//This Section Contains No Data - Nothing To Render
		return $retVal;
	}
	else
	{
		//Section Contains Data.  Add Section Header
		$retVal .= "<tr>
						<td class='infoboxComponentSectionHeader' colspan='2'>
							<center>General Specifications</center>
						</td>
					</tr>
					<tr>
							<td class='infoboxComponentLabel'>
								Size (W, D, H)
							</td>
							<td class='infoboxComponentValue'>";
		//Add Width
		if($width != '')
		{
			$retVal .= $width . " X ";
		}
		else
		{
			$retVal .= "- X ";
		}
		//Add Depth
		if($depth != '')
		{
			$retVal .= $depth . " X ";
		}
		else
		{
			$retVal .= "- X ";
		}
		//Add Height
		if($height != '')
		{
			$retVal .= $height;
		}
		else
		{
			$retVal .= "-";
		}
		//Add Pins
		if($width != '')
		{
			$retVal .= "<tr>
							<td class='infoboxComponentLabel'>
								Pins
							</td>
							<td class='infoboxComponentValue'>
								" . $pins . "
							</td>
						</tr>";
		}
		
		return $retVal;
	}
}
