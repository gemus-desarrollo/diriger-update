<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

class Tpage_control {
    //put your code hereclass Tpage_control {
    private $url;
    public $index;
    private $use_prev;
    private $count;

    public function __construct() {
        $this->url=array();
        $this->jumppage= false;
        $this->count= 1;
        $this->index= 1;

        $this->url[0]['url']= "../html/background.php?csfr_token={$_SESSION['csfr_token']}&";
        $this->url[0]['exec']= null;
        $this->url[0]['type']= 't';
    }

    public function set_jumppage($jumppage= false) {
      //  if ($jumppage) --$this->index;
        $this->jumppage= $jumppage;
    }

    public function add($url, $action='add', $type= 't') {
        $index= null;

        if ($type == 'f') {
            $count= ($this->count > 0) ? ($this->count - 1) : 0;

            for ($i= $count; $i > 0; $i--) {
                if (strcasecmp(substr($this->url[$i]['url'], 0, strpos($this->url[$i]['url'], ".php?")), substr($url, 0, strpos($url, ".php?"))) == 0)
                    $index= $i;
            }
        }
        if ($type == 'i') {
            for ($i= 0; $i < $this->count; $i++) {
                if (strcmp($this->url[$i]['url'], $url) == 0)
                    $index= $i;
            }
        }
        if ($type != 't' && is_null($index)) {
            $this->index= $this->count;
        }
        if ($type != 't' && !is_null($index)) {
            $this->index= $index;
        }
        if ($type == 't') {
            $this->index= 1;
        }

        $this->count= $this->index + 1;

        $this->url[$this->index]['url']= $url."&csfr_token={$_SESSION['csfr_token']}";
        $this->url[$this->index]['exec']= $action;
        $this->url[$this->index]['type']= $type;
    }

    public function next($error= null, $flag= null, $url_plus= null) {
        $action= $this->url[$this->index]['exec'];
        $type= $this->url[$this->index]['type'];
        ++$this->index;

        if ($flag)
            $this->index= 1;

        if ($this->index == $this->count && is_null($flag)) {
            if ($type == 'i' && $this->count >= 3) {
                if ($action == 'add' || $action == 'list' || $action == 'edit')
                    $this->index-= 3;
                if ($action == 'update')
                    $this->index-= 4;
                if ($action == 'delete')
                    $this->index-= 2;
                if ($this->index <= 0)
                    $this->index= 1;

                if ($this->url[$this->index]['exec'] == 'update' && $this->url[$this->index]['type'] == 'f')
                    --$this->index;
            }
            else
                $this->index= 0;
        }

        if (!is_null($error))
            $this->add_error($error);

        $this->go(null, $url_plus);
    }

    public function prev($error= null, $overwrite= true, $index= null, $url_plus= null) {
        $index= is_null($index) ? $this->index : $index;
        if ($index <= 1)
            $index= 1;

        if ($index == 1) {
            $this->go($index, $url_plus);
            return;
        }

        $n= 0;
        $this->use_prev= false;
        $url= $this->url[$index]['url'];

        do {
            --$index;
            ++$n;
            if ($index <= 1)
                break;
            if (!is_null($error) && ($this->url[$index]['type'] == 'f' && $this->url[$index]['exec'] == 'update'))
                break;

        } while (($this->url[$index]['type'] == 'f' && $this->url[$index]['exec'] == 'update')
                    || ($this->url[$index]['type'] == 'i'
                        && ($this->url[$index]['exec'] != 'edit' || ($this->url[$index]['exec'] == 'edit' && $n == 1))));

        if ($index <= 0)
            $index= 1;
        $k= strpos($this->url[$index]['url'], '?');
        $i= strpos($this->url[$index]['url'], '&error=');
        $j= strpos($url, '&error=');

        if ($i > 1 && $j > 1)
            $k= min($i, $j);
        elseif ($i > 1 && $j == 0)
            $k= $i;
        elseif ($i == 0 && $j > 0)
            $k= $j;

        if (is_null($error) && strncmp($url, $this->url[$index]['url'], $k) == 0) {
            if (!$overwrite)
                --$index;
            $this->prev(null, $overwrite, $index, $url_plus);
            return;
        }

        if ($index < 0)
            $index= 0;

        if (!is_null($error))
            $this->add_error($error, $index);

        $this->go($index, $url_plus);
    }

    private function go($index= null, $url_plus= null) {
        $index= is_null($index) ? $this->index : $index;
        if ($this->url[$index]['exect'] == 'delete' && $this->url[$index]['type'] == 'i')
            $index= 0;
        echo $this->url[$index]['url'].$url_plus;
    }

    private function add_error($error, $index= null) {
        $index= is_null($index) ? $this->index : $index;
        $this->url[$index]['url'].= '&error='.urlencode($error);
    }
}


global $page_control;

function add_page($url, $action='add', $type= 't') {
    global $page_control;

    if (isset($_SESSION['page_control']))
        $page_control= unserialize($_SESSION['page_control']);
    else
        $page_control= new Tpage_control();

    $page_control->add($url, $action, $type);
    $_SESSION['page_control']= serialize($page_control);
}

function set_page($url, $action='add', $type= 't') {
    global $page_control;

    if (isset($_SESSION['page_control']))
        unset($_SESSION['page_control']);
    unset($page_control);
    $page_control= new Tpage_control();

    $page_control->add($url, $action, $type);
    $_SESSION['page_control']= serialize($page_control);
}

function next_page($error= null, $flag= null, $url_plus= null) {
    global $page_control;

    $page_control= unserialize($_SESSION['page_control']);
    $page_control->next($error, $flag, $url_plus);
    $_SESSION['page_control']= serialize($page_control);
}

/**
 * @param null $error
 * @param bool $overwrite
 * @param bool $jumppage true que no paso por una interface (interface.class)
 */
function prev_page($error= null, $overwrite= true, $jumppage= false, $url_plus= null) {
    global $page_control;

    $page_control= unserialize($_SESSION['page_control']);
    $page_control->set_jumppage($jumppage);
    $page_control->prev($error, $overwrite, null, $url_plus);
    $page_control->set_jumppage(false);
    $_SESSION['page_control']= serialize($page_control);
}
