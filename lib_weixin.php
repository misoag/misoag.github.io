<?php
$url    = "http://mp.weixin.qq.com/mp/appmsg/show?__biz=MjM5Mzg0NjU5Mw==&appmsgid=10000566&itemidx=4&sign=a35c558d1e98e837b1fc401079865d08&scene=1#wechat_redirect";

echo fetchWeixin($url);

function fetchWeixin($url){
    $html       = getWeixinHTML($url);
    $content    = getWeixinContent($html);
    $images     = getWeixinContentImages($content);

    foreach($images as $img_src){
        $image      = getImage($img_src,'./images/','',1);
        $value      = './images/'.$image['file_name'];
        $content    = str_replace($img_src,$value,$content);
    }


    $content = str_replace('data-src','src',$content);
    $fileName = md5($url);
    writeFile("./{$fileName}.html",$content);
}

function getWeixinHTML($url)
{
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
}

function getWeixinContent($html)
{
        $pattern = "/<div\s+class=\"text\">\s+.*<\/div>/"; 
        preg_match($pattern,$html,$match); 
        return $match[0];
}

function getWeixinContentImages($content)
{
        $pattern = "/data-src=\"(.*?)\"/"; 
        preg_match_all($pattern,$content,$match);
        if(count($match)>1 && count($match[1])>0){
            return $match[1];
        }
        return array();
}

function getImage($url,$save_dir='',$filename='',$type=0){
    if(empty($url)){
        return array('file_name'=>'','save_path'=>'','error'=>1);
    }
    
    if(empty($save_dir)){
        return array('file_name'=>'','save_path'=>'','error'=>2);
    }
    
    if(substr($save_dir, -1) !== '/'){
        $save_dir .= '/';
    }
    
    if(trim($filename)==''){
        $filename = md5($url);
    }
    
    //创建目录
    if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
        return array('file_name'=>'','save_path'=>'','error'=>5);
    }
    //获取远程文件所采用的方法 
    if($type){
        $ch=curl_init();
        $timeout=5;
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        $img=curl_exec($ch);
        curl_close($ch);
    }else{
        ob_start(); 
        readfile($url);
        $img=ob_get_contents(); 
        ob_end_clean(); 
    }
    //$size=strlen($img);
    //文件大小 
    $fp2=@fopen($save_dir.$filename,'a');
    fwrite($fp2,$img);
    fclose($fp2);
    unset($img,$url);
    return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0);
}


function writeFile($fileName,$content){
    $fh = fopen($fileName, 'w');
    fwrite($fh, $content);
    fclose($fh);
}
?>