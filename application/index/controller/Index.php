<?php
namespace app\index\controller;

use app\index\model\User;
use think\Request;
class Index extends Base
{
    public function index()
    {
        dump(1111);
    }

    public function save(Request $request)
    {
        dump(2222);	
    }

    public function read($id)
    {
        dump(33333); 
    }

    public function update(Request $request, $id)
    {
        dump(22221412421); 
    }

    public function delete($id)
    {
        dump(2224712712741892); 
    }
    
}
