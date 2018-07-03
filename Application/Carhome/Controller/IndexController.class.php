<?php
namespace Carhome\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        $this->display();
    }
    public function person(){
        $this->display();
    }
    public function xiaohu(){
        $this->display();
    }
    public function kuai(){
        $this->display();
    }
    public function verify()
    {
        import('ORG.Util.Image');
        Image::buildImageVerify();
    }
}