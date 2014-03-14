<?php
/**
 * DokuWiki Plugin shorty (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Fernando Ribeiro <pinguim.ribeiro@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class syntax_plugin_shorty extends DokuWiki_Syntax_Plugin {
    /**
     * @return string Syntax mode type
     */
    public function getType() {
        return 'substition';
    }
    /**
     * @return string Paragraph type
     */
    public function getPType() {
        return 'normal';
    }
    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort() {
        return 999;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        // shorty plugin sintax:
        // ~~shorty~~
        // ~~shorty service~~
        $this->Lexer->addSpecialPattern('~~shorty\b.*?~~',$mode,'plugin_shorty');
    }


    /**
     * Handle matches of the shorty syntax
     *
     * @param string $match The match of the syntax
     * @param int    $state The state of the handler
     * @param int    $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler){
        // disable bitly plugin sintax if we are parsing a submitted comment...
        if (isset($_REQUEST['comment'])) return false;

        $match = substr($match, 8, -2);         //strip ~~shorty from start and ~~ from end
        $service = strtolower(trim($match));    //strip spaces

        if (!$service) return $this->getConf('default_service');
        return $service;
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer &$renderer, $data) {
        global $ID;
        if($data == false) return false;

        if($mode != 'xhtml') return false;

        $shorty =& plugin_load('helper', 'shorty');
        if ($shorty) {
            $shortUrl = $shorty->getShortUrl($ID, $data);
            if ($shortUrl != false) {
                $renderer->doc .= $renderer->externallink($shortUrl);
            } else {
                $renderer->doc .= "Shorty: error generating short URL.";
            }
        }
        return true;
    }
}

// vim:ts=4:sw=4:et:
