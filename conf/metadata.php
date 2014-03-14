<?php
/**
 * Options for the shorty plugin
 *
 * @author Fernando Ribeiro <pinguim.ribeiro@gmail.com>
 */

 
 
$meta['bitly_oauth_api']            = array('string');
$meta['bitly_oauth_access_token']   = array('string');

$meta['default_service']            = array('multichoice',
                                            '_choices' => array('bit.ly',
                                                                'bitly.com',
                                                                'j.mp',
                                                                'tinyurl'
                                                                ));
