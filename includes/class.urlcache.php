<?PHP
    abstract class URLCache
    {
        // You can't call me
        private function __construct() {}

        // Must return the appropriate decoded object or false on failure
        abstract public static function decodeStrData($str);

        public static function getData($url, $expires_in = 300)
        {
            $str = self::getDataStr($url, $expires_in);
            if($str === false) return false;
            return self::decodeStrData($str);
        }

        public static function getDataStr($url, $expires_in = 300)
        {
            $db = Database::getDatabase();
            $db->query("SELECT * FROM url_cache WHERE url = :url LIMIT 1", array('url' => $url));
            $row = $db->getRow();

            if($row === false)
            {
                return self::refreshContent($url, $expires_in);
            }
            elseif(strtotime($row['dt_expires']) < time())
            {
                $data = self::refreshContent($url, $expires_in);
                return ($data === false) ? $row['data'] : $data;
            }
            else
            {
                return $row['data'];
            }
        }

        public static function refreshContent($url, $expires_in = 300)
        {
            $str = self::getURL($url);
            $data = self::decodeStrData($str);
            if($data === false) return false;

            $db = Database::getDatabase();
            $db->query("REPLACE INTO url_cache (url, dt_refreshed, dt_expires, data) VALUES (:url, :dt_refreshed, :dt_expires, :data)",
                       array('url'          => $url,
                             'dt_refreshed' => dater(),
                             'dt_expires'   => dater(time() + $expires_in),
                             'data'         => $str));
            return $str;
        }

        private static function getURL($url)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            // curl_setopt($ch, CURLOPT_VERBOSE, 1);
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        }
    }

    class XMLCache extends URLCache
    {
        public static function decodeStrData($str)
        {
            return simplexml_load_string($str);
        }
    }

    class JSONCache extends URLCache
    {
        public static function decodeStrData($str)
        {
            return json_decode($str);
        }
    }
