<?php
    // server base class
    require_once 'Net/Server.php';

    // base class for the handler
    require_once 'Net/Server/Handler.php';

     require_once 'HTMLPurifier.auto.php';

     // Include the Console_CommandLine package.
     require_once 'Console/CommandLine.php';

     // create the parser
     $parser = new Console_CommandLine(array(
         'description' => 'Simple echo serive that sanitizes html using htmlPurifer',
         'version'     => '1.0.0'
     ));

     // add an option to make the program verbose
     $parser->addOption(
         'hostip',
         array(
             'short_name'  => '-h',
             'long_name'   => '--host-ip',
             'action'      => 'StoreString',
             'description' => 'ip address to listen on.',
             'default'     => '127.0.0.1'
         )
     );

   $parser->addOption(
       'hostport',
       array(
           'short_name'  => '-p',
           'long_name'   => '--host-port',
           'action'      => 'StoreString',
           'description' => 'port number to listen on.',
           'default'     => '6242'
       )
   );

     // run the parser
     try {
         $result = $parser->parse();
     } catch (Exception $exc) {
         $parser->displayError($exc->getMessage());
         exit;
     }


     // write your program here...
     $host_ip = $result->options['hostip'] ;
     $host_port = $result->options['hostport'] ;

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

/**
 * simple example that implements a talkback.
 *
 * Normally this should be a bit more code and in a separate file
 */
class Net_Server_Handler_Talkback extends Net_Server_Handler
{
   /**
    * If the user sends data, send it back to him
    *
    * @access   public
    * @param    integer $clientId
    * @param    string  $data
    */
    public $document = "";

    function    onReceiveData( $clientId = 0, $data = "" )
    {
        if (trim($data) == "STOP") {
          $this->_server->sendData( $clientId,  sanitize_html($this->document) );
          $this->_server->closeConnection();
        } else {
          $this->document .= $data;
        }
    }
}

    // create a server that forks new processes
    $server  = &Net_Server::create('fork', $host_ip, $host_port);
    $server->setDebugMode(FALSE);

    $handler = &new Net_Server_Handler_Talkback;

    // hand over the object that handles server events
    $server->setCallbackObject($handler);

    // start the server
    $server->start();
?>