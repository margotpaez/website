<?php
/**
 * Wiki Statistics Plugin: Displays some wiki stats
 *
 * @author     Emanuele <emanuele45@interfree.it>
 */
 
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once DOKU_PLUGIN.'action.php';
 
class action_plugin_wikistatistics extends DokuWiki_Action_Plugin {
 
	/**
	 * return some info
	 */
	function getInfo(){
		return array(
			'author' => 'Emanuele, Thomas',
			'email'  => 'emanuele45@interfree.it',
			'date'   => '2010-02-xx',
			'name'   => 'WikiStatistics',
			'desc'   => 'Display statistics about the Wiki and their users',
			'url'	 => 'http://lacroa.altervista.org/dokucount/',
		);
	}
 
	/**
	 * Register its handlers with the DokuWiki's event controller
	 */
	function register(&$controller) {
		$controller->register_hook('TPL_CONTENT_DISPLAY', 'BEFORE', $this,
								   '_clean_xhtml');
	}
 
	/**
	 * 
	 *
	 *
	 */
	function _clean_xhtml(&$event, $param) {
		$pattern = '/(<p>(.*?)<table class="wikistat (.*?)<\/p>)/ism';
		$replace = '<p>$2</p><table class="wikistat $3';
		$event->data = preg_replace($pattern, $replace,$event->data);
	}
}

