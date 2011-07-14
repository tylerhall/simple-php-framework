<?PHP
    // Track your page with...
    // Stats::track($some_page_title);

    class Stats
    {
        private static $me;

        private function __construct()
        {

        }

        public function getStats()
        {
            if(is_null(self::$me))
                self::$me = new Stats();
            return self::$me;
        }

        public static function track($page_title = '')
        {
            $db = Database::getDatabase();

            $dt               = dater();
            $referer          = getenv('HTTP_REFERER');
            $referer_is_local = self::refererIsLocal($referer);
            $url              = full_url();
            $search_terms     = self::searchTerms();
            $img_search       = '';
            $ip               = self::getIP();
            $info             = self::browserInfo();
            $browser_family   = $info['browser'];
            $browser_version  = $info['version'];
            $os               = $info['platform'];
            $os_version       = '';
            $user_agent       = $info['useragent'];

            $exec_time = defined('START_TIME') ? microtime(true) - START_TIME : 0;
            $num_queries = $db->numQueries();

            $sql = "INSERT INTO stats (dt, referer, referer_is_local, url, page_title, search_terms, img_search, browser_family, browser_version, os, os_version, ip, user_agent, exec_time, num_queries)
                    VALUES (:dt, :referer, :referer_is_local, :url, :page_title, :search_terms, :img_search, :browser_family, :browser_version, :os, :os_version, :ip, :user_agent, :exec_time, :num_queries)";
            $vals = array('dt'               => $dt,
                          'referer_is_local' => $referer_is_local,
                          'referer'          => $referer,
                          'url'              => $url,
                          'page_title'       => $page_title,
                          'search_terms'     => $search_terms,
                          'img_search'       => $img_search,
                          'ip'               => $ip,
                          'browser_family'   => $browser_family,
                          'browser_version'  => $browser_version,
                          'os_version'       => $os_version,
                          'os'               => $os,
                          'user_agent'       => $user_agent,
                          'exec_time'        => $exec_time,
                          'num_queries'      => $num_queries);
            $db->query($sql, $vals);
        }

        public static function refererIsLocal($referer = null)
        {
            if(is_null($referer)) $referer = getenv('HTTP_REFERER');
            if(!strlen($referer)) return 0;
            $regex_host = preg_quote(getenv('HTTP_HOST'));
            return (preg_match("!^https?://$regex_host!i", $referer) !== false) ? 1 : 0;
        }

        public static function getIP()
        {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
            if(!$ip) $ip = getenv('HTTP_CLIENT_IP');
            if(!$ip) $ip = getenv('REMOTE_ADDR');
            return $ip;
        }

        public static function searchTerms($url = null)
        {
            if(is_null($url)) $url = full_url();
            // if(self::refererIsLocal($url)) return;

            $arr = array();
            parse_str(parse_url($url, PHP_URL_QUERY), $arr);

            return isset($arr['q']) ? $arr['q'] : '';
        }

        // From http://us3.php.net/get_browser comments
        public static function browserInfo($a_browser = false, $a_version = false, $name = false)
        {
            $browser_list = 'msie firefox konqueror chrome safari netscape navigator opera mosaic lynx amaya omniweb avant camino flock seamonkey aol mozilla gecko';
            $user_browser = strtolower(getenv('HTTP_USER_AGENT'));
            $this_version = $this_browser = '';

            $browser_limit = strlen($user_browser);
            foreach(explode(' ', $browser_list) as $row)
            {
                $row = ($a_browser !== false) ? $a_browser : $row;
                $n = stristr($user_browser, $row);
                if(!$n || !empty($this_browser)) continue;

                $this_browser = $row;
                $j = strpos($user_browser, $row) + strlen($row) + 1;
                for(; $j <= $browser_limit; $j++)
                {
                    $s = trim(substr($user_browser, $j, 1));
                    $this_version .= $s;

                    if($s === '') break;
                }
            }

            if($a_browser !== false)
            {
                $ret = false;
                if(strtolower($a_browser) == $this_browser)
                {
                    $ret = true;

                    if($a_version !== false && !empty($this_version))
                    {
                        $a_sign = explode(' ', $a_version);
                        if(version_compare($this_version, $a_sign[1], $a_sign[0]) === false)
                        {
                            $ret = false;
                        }
                    }
                }

                return $ret;
            }

            $this_platform = '';
            if(strpos($user_browser, 'linux'))
            {
                $this_platform = 'linux';
            }
            elseif(strpos($user_browser, 'macintosh') || strpos($user_browser, 'mac platform x'))
            {
                $this_platform = 'mac';
            }
            elseif(strpos($user_browser, 'windows') || strpos($user_browser, 'win32'))
            {
                $this_platform = 'windows';
            }

            if($name !== false)
            {
                return $this_browser . ' ' . $this_version;
            }

            return array("browser"   => $this_browser,
                         "version"   => $this_version,
                         "platform"  => $this_platform,
                         "useragent" => $user_browser);
        }
    }
