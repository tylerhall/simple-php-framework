<?PHP
    // Simple wrapper class for GD
    class GD
    {
        public $im     = null;
        public $width  = null;
        public $height = null;
        public $type   = null;
        public $mime   = null;

        public function __construct($data = null, $ext = null)
        {
            if(is_resource($data) && get_resource_type($data) == 'gd')
                return $this->loadResource($data);
            elseif(@file_exists($data) && is_readable($data))
                return $this->loadFile($data);
            elseif(is_string($data))
                return $this->loadString($data);
            else
                return false;
        }

        private function loadResource($im)
        {
            if(!is_resource($im) || !get_resource_type($im) == 'gd') return false;

            $this->im     = $im;
            $this->width  = imagesx($im);
            $this->height = imagesy($im);

            return true;
        }

        private function loadFile($filename)
        {
            if(!file_exists($filename) || !is_readable($filename)) return false;

            $info = getimagesize($filename);
            $this->width  = $info[0];
            $this->height = $info[1];
            $this->type   = image_type_to_extension($info[2], false);
            $this->mime   = $info['mime'];

            if($this->type == 'jpeg' && (imagetypes() & IMG_JPG))
                $this->im = imagecreatefromjpeg($filename);
            elseif($this->type == 'png' && (imagetypes() & IMG_PNG))
                $this->im = imagecreatefrompng($filename);
            elseif($this->type == 'gif' && (imagetypes() & IMG_GIF))
                $this->im = imagecreatefromgif($filename);
            else
                return false;

            return true;
        }

        private function loadString($str)
        {
            $im = imagecreatefromstring($str);
            return ($im === false) ? false : $this->loadResource($im);
        }

        public function saveAs($filename, $type = 'jpg', $quality = 75)
        {
            if($type == 'jpg' && (imagetypes() & IMG_JPG))
                return imagejpeg($this->im, $filename, $quality);
            elseif($type == 'png' && (imagetypes() & IMG_PNG))
                return imagepng($this->im, $filename);
            elseif($type == 'gif' && (imagetypes() & IMG_GIF))
                return imagegif($this->im, $filename);
            else
                return false;
        }

        // Output file to browser
        public function output($type = 'jpg', $quality = 75)
        {
            if($type == 'jpg' && (imagetypes() & IMG_JPG))
            {
                header("Content-Type: image/jpeg");
                imagejpeg($this->im, null, $quality);
                return true;
            }
            elseif($type == 'png' && (imagetypes() & IMG_PNG))
            {
                header("Content-Type: image/png");
                imagepng($this->im);
                return true;
            }
            elseif($type == 'gif' && (imagetypes() & IMG_GIF))
            {
                header("Content-Type: image/gif");
                imagegif($this->im);
                return true;
            }
            else
                return false;
        }

        // Return image data as a string.
        // Is there a way to do this without using output buffering?
        public function __tostring($type = 'jpg', $quality = 75)
        {
            ob_start();

            if($type == 'jpg' && (imagetypes() & IMG_JPG))
                imagejpeg($this->im, null, $quality);
            elseif($type == 'png' && (imagetypes() & IMG_PNG))
                imagepng($this->im);
            elseif($type == 'gif' && (imagetypes() & IMG_GIF))
                imagegif($this->im);

            return ob_get_clean();
        }

        // Resizes an image and maintains aspect ratio.
        public function scale($new_width = null, $new_height = null)
        {
            if(!is_null($new_width) && is_null($new_height))
                $new_height = $new_width * $this->height / $this->width;
            elseif(is_null($new_width) && !is_null($new_height))
                $new_width = $this->width / $this->height * $new_height;
            elseif(!is_null($new_width) && !is_null($new_height))
            {
                if($this->width < $this->height)
                    $new_width = $this->width / $this->height * $new_height;
                else
                    $new_height = $new_width * $this->height / $this->width;
            }
            else
                return false;

            return $this->resize($new_width, $new_height);
        }

        // Resizes an image to an exact size
        public function resize($new_width, $new_height)
        {
            $dest = imagecreatetruecolor($new_width, $new_height);

            // Transparency fix contributed by Google Code user 'desfrenes'
            imagealphablending($dest, false);
            imagesavealpha($dest, true);

            if(imagecopyresampled($dest, $this->im, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height))
            {
                $this->im = $dest;
                $this->width = imagesx($this->im);
                $this->height = imagesy($this->im);
                return true;
            }

            return false;
        }

        public function crop($x, $y, $w, $h)
        {
            $dest = imagecreatetruecolor($w, $h);

            if(imagecopyresampled($dest, $this->im, 0, 0, $x, $y, $w, $h, $w, $h))
            {
                $this->im = $dest;
                $this->width = $w;
                $this->height = $h;
                return true;
            }

            return false;
        }

        public function cropCentered($w, $h)
        {
            $cx = $this->width / 2;
            $cy = $this->height / 2;
            $x = $cx - $w / 2;
            $y = $cy - $h / 2;
            if($x < 0) $x = 0;
            if($y < 0) $y = 0;
            return $this->crop($x, $y, $w, $h);
        }
    }
