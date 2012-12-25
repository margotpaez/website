<?php
/********************************************************************************************************************************
*
* LabVIEW Hacker Instruction Step Template Plugin
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
class syntax_plugin_lvhcompelectrical extends DokuWiki_Syntax_Plugin 
{

	//Return Plugin Info
	function getInfo() 
	{
        return array('author' => 'Sammy_K',
                     'email'  => 'sammyk.labviewhacker@gmail.com',
                     'date'   => '2012-12-21',
                     'name'   => 'LabVIEW Hacker Component Electrical Template Plugin',
                     'desc'   => 'Template for LabVIEW Hacker Componenent Electrical Table',
                     'url'    => 'www.labviewhacker.com');
    }

	//Set This To True To Enable Debug Strings
	protected $lvhDebug = false;
	
	//Quick Customizations
	protected $maxImageSize = 200;
	
	/***************************************************************************************************************************
	* Plugin Variables
	***************************************************************************************************************************/
	protected $vccMin = '';	
	protected $vccTypical = '';	
	protected $vccMax = '';	
	protected $vccUnits = '';	
	protected $iccMin = '';	
	protected $iccTypical = '';	
	protected $iccMax = '';	
	protected $iccUnits = '';	
	protected $powerMin = '';	
	protected $powerTypical = '';	
	protected $powerMax = '';	
	protected $powerUnits = '';	
	protected $llMin = '';	
	protected $llTypical = '';	
	protected $llMax = '';	
	protected $llUnits = '';	
	
	protected $tempVal = 'empty';
  
    function getType() { return 'protected'; }
    function getSort() { return 32; }
  
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('{{lvh_comp_electrical.*?(?=.*?}})',$mode,'plugin_lvhcompelectrical');
		
		//Add Internal Pattern Match For Product Page Elements	
		$this->Lexer->addPattern('\|.*?(?=.*?)\n','plugin_lvhcompelectrical');
    }
	
    function postConnect() {
      $this->Lexer->addExitPattern('}}','plugin_lvhcompelectrical');
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
					case 'vcc min':						
						$this->vccMin = $value;
						break;	
					case 'vcc typical':						
						$this->vccTypical = $value;
						break;						
					case 'vcc max':						
						$this->vccMax = $value;
						break;
					case 'vcc units':						
						$this->vccUnits = $value;
						break;	
					case 'icc min':						
						$this->iccMin = $value;
						break;	
					case 'icc typical':						
						$this->iccTypical = $value;
						break;
					case 'icc max':						
						$this->iccMax = $value;
						break;	
					case 'icc units':						
						$this->iccUnits = $value;
						break;						
					case 'power min':						
						$this->powerMin = $value;
						break;						
					case 'power typical':						
						$this->powerTypical = $value;
						break;
					case 'power max':						
						$this->powerMax = $value;
						break;
					case 'power units':						
						$this->powerUnits = $value;
						break;
					case 'logic level min':						
						$this->llMin = $value;
						break;
					case 'logic level typical':						
						$this->llTypical = $value;
						break;
					case 'logic level max':						
						$this->llMax = $value;
						break;	
					case 'logic level units':						
						$this->llUnits = $value;
						break;	
					default:
						break;
				}
				return array($state, $value);
				break;
			case DOKU_LEXER_UNMATCHED :
				break;
			case DOKU_LEXER_EXIT :
				$retVal = array($state, $this->vccMin, $this->vccTypical, $this->vccMax, $this->vccUnits, $this->iccMin, $this->iccTypical, $this->iccMax, $this->iccUnits, $this->powerMin, $this->powerTypical, $this->powerMax, $this->powerUnits, $this->llMin, $this->llTypical, $this->llMax, $this->llUnits);
				
				//Clear Variables For Next Call (Not Sure If This Is Necissary)
				$vccMin = '';	
				$vccTypical = '';	
				$vccMax = '';	
				$vccUnits = '';	
				$iccMin = '';	
				$iccTypical = '';	
				$iccMax = '';	
				$iccUnits = '';	
				$powerMin = '';	
				$powerTypical = '';	
				$powerMax = '';	
				$powerUnits = '';	
				$llMin = '';	
				$llTypical = '';	
				$llMax = '';	
				$llUnits = '';	
				
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
				if($this->lvhDebug) $renderer->doc .= 'MATCHED';	//Debug				
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
				$instVccMin = $data[1];	
				$instVccTypical = $data[2];	
				$instVccMax = $data[3];	
				$instVccUnits = $data[4];	
				$instIccMin = $data[5];	
				$instIccTypical = $data[6];	
				$instIccMax = $data[7];	
				$instIccUnits = $data[8];	
				$instPowerMin = $data[9];	
				$instPowerTypical = $data[10];	
				$instPowerMax = $data[11];	
				$instPowerUnits = $data[12];	
				$instLlMin = $data[13];	
				$instLlTypical = $data[14];	
				$instLlMax = $data[15];	
				$instLlUnits = $data[16];	
				
				
				//Build Optional Table Rows
				$vccRow = '';
				$iccRow = '';
				$powerRow = '';
				$llRow = '';
				$lightRow = true;
				
				if( !( trim($instVccMin) == '') || !( trim($instVccTypical) == '') || !( trim($instVccMax) == ''))
				{
					//At Least One VCC Given, Generate Code
					//Set Row CSS Class To Highlighter Alternate Rowns
					if($lightRow)
					{
						$vccRow =  "<tr class='comElectricalBodyRowLight'>";
						$lightRow = false;
					}
					else
					{
						$vccRow =  "<tr class='comElectricalBodyRowDark'>";
						$lightRow = true;
					}
					$vccRow .=  "	<td>
										<center><b>Vcc</b></center>
									</td>
									<td>
										<center>" . $instVccMin . "</center>
									</td>
									<td>
										<center>" . $instVccTypical . "</center>
									</td>
									<td>
										<center>" . $instVccMax . "</center>
									</td>
									<td>
										<center>" . $instVccUnits . "</center>
									</td>									
								</tr>";
				}
				
				if( !( trim($instIccMin) == '') || !( trim($instIccTypical) == '') || !( trim($instIccMax) == ''))
				{
					//At Least One VCC Given, Generate Code
					//Set Row CSS Class To Highlighter Alternate Rowns
					if($lightRow)
					{
						$iccRow =  "<tr class='comElectricalBodyRowLight'>";
						$lightRow = false;
					}
					else
					{
						$iccRow =  "<tr class='comElectricalBodyRowDark'>";
						$lightRow = true;
					}
					$iccRow .=  "	<td>
										<center><b>Icc</b></center>
									</td>
									<td>
										<center>" . $instIccMin . "</center>
									</td>
									<td>
										<center>" . $instIccTypical . "</center>
									</td>
									<td>
										<center>" . $instIccMax . "</center>
									</td>
									<td>
										<center>" . $instIccUnits . "</center>
									</td>									
								</tr>";
				}
				
				if( !( trim($instPowerMin) == '') || !( trim($instPowerTypical) == '') || !( trim($instPowerMax) == ''))
				{
					//At Least One VCC Given, Generate Code
					//Set Row CSS Class To Highlighter Alternate Rowns
					if($lightRow)
					{
						$powerRow =  "<tr class='comElectricalBodyRowLight'>";
						$lightRow = false;
					}
					else
					{
						$vccRow =  "<tr class='comElectricalBodyRowDark'>";
						$powerRow = true;
					}
					$powerRow .=  "	<td>
										<center><b>Power</b></center>
									</td>
									<td>
										<center>" . $instPowerMin . "</center>
									</td>
									<td>
										<center>" . $instPowerTypical . "</center>
									</td>
									<td>
										<center>" . $instPowerMax . "</center>
									</td>
									<td>
										<center>" . $instPowerUnits . "</center>
									</td>									
								</tr>";
				}
				if( !( trim($instLlMin) == '') || !( trim($instLlTypical) == '') || !( trim($instLlMax) == ''))
				{
					//At Least One VCC Given, Generate Code
					//Set Row CSS Class To Highlighter Alternate Rowns
					if($lightRow)
					{
						$llRow =  "<tr class='comElectricalBodyRowLight'>";
						$lightRow = false;
					}
					else
					{
						$llRow =  "<tr class='comElectricalBodyRowDark'>";
						$lightRow = true;
					}
					$llRow .=  "<td>
										<center><b>Logic Level</b></center>
									</td>
									<td>
										<center>" . $instLlMin . "</center>
									</td>
									<td>
										<center>" . $instLlTypical . "</center>
									</td>
									<td>
										<center>" . $instLlMax . "</center>
									</td>
									<td>
										<center>" . $instLlUnits . "</center>
									</td>									
								</tr>";
				}		
			
				
				$renderer->doc .= "
					<head>
						<style type='text/css'>						
							table.comElectrical
							{  
								width:66%;
								border:1px solid black;
								border-collapse:collapse;
								float:left;
								margin:10px;								
							}
							.comElectricalTitleRow
							{ 		
								border:1px solid #AAAAAA;	
								horizontal-align:middle;
								vertical-align:middle;									
								background-color:#CCCCCC;
							}
							.comElectricalTitleCell
							{ 		
								border:1px solid #AAAAAA;	
								horizontal-align:middle;
								vertical-align:middle;									
								background-color:#CCCCCC;
							}
							.comElectricalBodyRowLight
							{ 		
								border:1px solid black;	
								horizontal-align:middle;
								vertical-align:middle;	
								background-color:white;
							}
							.comElectricalBodyRowDark
							{ 		
								border:1px solid black;	
								horizontal-align:middle;
								vertical-align:middle;	
								background-color:#EEEEEE;
							}
						</style>
					</head>

					<body>
						<table class='comElectrical'>
							<tr class='comElectricalTitleRow'>
								<td class='comElectricalTitleCell'>
									<center><b>Symbol</b></center>
								</td>
								<td class='comElectricalTitleCell'>
									<center><b>Minimum</b></center>
								</td>
								<td class='comElectricalTitleCell'>
									<center><b>Typical</b></center>
								</td>
								<td class='comElectricalTitleCell'>
									<center><b>Maximum</b></center>
								</td>
								<td class='comElectricalTitleCell'>
									<center><b>Units</b></center>
								</td>
							</tr>
							" . $vccRow . 
							    $iccRow . 
								$powerRow . 
								$llRow . "
                            							
						</table>
								
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
	