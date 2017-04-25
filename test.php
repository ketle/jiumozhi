<?php
if ($_POST) {
    require './vendor/autoload.php';
    include('Crawler/PauseData.php');
    print_r($_POST);//die;
    echo "<br /><br />";

    $content = file_get_contents(trim($_POST['url']));
    //echo $content;

    $content = mb_convert_encoding($content, "utf-8",trim($_POST['charset']));
    $content = preg_replace('|charset\s*=\s*(\w+)|i', 'charset=UTF-8', $content);
    $pauseDrive = PauseFactory::Create( trim($_POST['drive']) );
    //echo $content;
    $data = $pauseDrive->pause($content,trim($_POST['selector']));

    //print_r($pauseDrive);
    print_r($data);
    echo "<br /><br />";
}

?>

<form  method="post">
    

    url: <input type="text" name="url" size="100" value="<?=$_POST['url']?>" > <br />
    charset: <input type="text" name="charset" size="100" value="<?=$_POST['charset']?$_POST['charset']:'utf-8'?>" > <br />
    selector: <input type="text" name="selector"  size="100" value="<?=$_POST['selector']?>"> <br />
    drive: <input type="text" name="drive" value="<?=$_POST['drive']?$_POST['drive']:'XPath'?>">
    <input type="submit">
</form>