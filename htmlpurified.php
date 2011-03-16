#!/usr/bin/php
<?php


     require_once 'HTMLPurifier.auto.php';
     class MaiaDisplayLinkURI extends HTMLPurifier_Injector
     {

         public $name = 'DisplayLinkURI';
         public $needed = array('a');

         private $idcount = 1;

         public function handleElement(&$token) {
         }

         public function handleEnd(&$token) {
             if (isset($token->start->attr['href'])){
                 $url =  MaiaDisplayLinkURI::pretty_url($token->start->attr['href']);
                 unset($token->start->attr['href']);
                 $token->start->attr['class'] = 'DisplayLink';
                 $token->start->attr['id'] = 'DisplayLink_' . $this->idcount;

                 $token = array_merge(array($token,
                         new HTMLPurifier_Token_Start('span', array('class' => 'DisplayLinkURL', 'id'=>'tip_DisplayLink_' . $this->idcount))),
                         $url,
                         array(
                         new HTMLPurifier_Token_End('span')
                         ));
                 $this->idcount += 1;
             } else {
                 // nothing to display
             }
         }

         private static function color_tokens($part, $class) {
             $token = array(
                 new HTMLPurifier_Token_Start('font', array('class' => "DisplayLink_" . $class)),
                    new HTMLPurifier_Token_Text($part),
                    new HTMLPurifier_Token_End('font')
                 );
              return $token;
         }

         private static function pretty_url($url) {
           // Make sure we have a string to work with
           if(!empty($url)) {
             // Explode into URL keys
             $urllist=parse_url($url);

             // Make sure we have a valid result set and a query field
             if(is_array($urllist) ) {
             // Build the the final output URL
               $newurl=array();
               if (isset($urllist["scheme"]))   {$newurl = array_merge($newurl, MaiaDisplayLinkURI::color_tokens(($urllist['scheme'] . "://"),"scheme")); }
               if (isset($urllist["user"]))     {$newurl = array_merge($newurl, MaiaDisplayLinkURI::color_tokens($urllist["user"] . ":", "user")); }
               if (isset($urllist["pass"]))     {$newurl = array_merge($newurl, MaiaDisplayLinkURI::color_tokens($urllist["pass"] . "@", "pass")); }
               if (isset($urllist["host"]))     {$newurl = array_merge($newurl, MaiaDisplayLinkURI::color_tokens($urllist["host"], "host")); }
               if (isset($urllist["port"]))     {$newurl = array_merge($newurl, MaiaDisplayLinkURI::color_tokens(":" . $urllist["port"], "port")); }
               if (isset($urllist["path"]))     {$newurl = array_merge($newurl, MaiaDisplayLinkURI::color_tokens($urllist["path"], "path")); }
               if (isset($urllist["query"]))    {$newurl = array_merge($newurl, MaiaDisplayLinkURI::color_tokens(("?" . $urllist["query"]), "query")); }
               if (isset($urllist["fragment"])) {$newurl = array_merge($newurl, MaiaDisplayLinkURI::color_tokens("#" . $urllist["fragment"], "fragment")); }
               return $newurl;
             }
           }
           return array();
         }
     }


     function sanitize_html($body)
     {
         global $purifier_cache;
         if (!isset($purifier_cache)) {
             $purifier_cache = null;
         }

         $config = HTMLPurifier_Config::createDefault();
         if ($purifier_cache) {
             $config->set('Cache.SerializerPath', $purifier_cache);
         } else {
             $config->set('Cache.DefinitionImpl', null);
         }
         $config->set('URI.Disable', true);
         $config->set('Attr.EnableID', true);
         $config->set('AutoFormat.Custom', array(new MaiaDisplayLinkURI));
         $purifier = new HTMLPurifier($config);

         $html =  $purifier->purify($body);

         return ($html);
     }

     $document=file_get_contents("php://stdin");

     $sanitized = sanitize_html($document);

     print $sanitized;

?>