<?php

namespace jaslin\yii2\composer;

use Composer\Script\Event;
use Composer\Util\Filesystem;

class ComposerScripts 
{

    
    public static function postAutoloadDump(Event $event)
    {
        $composer = $event->getComposer();
        $filesystem = new FileSystem();
        $config = $composer->getConfig();
        $package = $composer->getPackage();
        $extra = $package->getExtra();
        $psr4config = $extra['local-psr-4'];
        $yii2LocalExtensions = $extra['yii2-local-extensions'];
        
        $yii2conifg = '';
        foreach($yii2LocalExtensions as $extension){
           $yii2conifg .= self::genYii2ExtensionConfig($extension);
        }
        $psr4File = $filesystem->normalizePath(realpath($config->get('vendor-dir').'/composer/autoload_psr4.php'));
        $yii2extensionFile = $filesystem->normalizePath(realpath($config->get('vendor-dir').'/yiisoft/extensions.php'));

        self::appendBeforeLastline($psr4config,$psr4File);
        self::appendBeforeLastline($yii2conifg,$yii2extensionFile);

    }
    /**
     *
     * 输入一个yii2扩展的配置 生成,一个符合yiisoft/extensions.php格式的配置
     *
     * @param array $extension 一个扩展配置,配置应该是一个数字['name'=>'','version'=>'','alias'=>'','path'=>'']
     */
    public static function genYii2ExtensionConfig($extension)
    {
        $template = "
    '{name}' => 
    array(
        'name' => '{name}',
        'version' => '{version}',
        'alias' => 
            array(
                '{alias}'=>{path}
            )
    ),";
        $search = array('{name}','{version}','{alias}','{path}');
        $config = str_replace($search,$extension,$template);
        echo 'config......\n';
        var_dump($config);
        return $config;
    }

    /**
     *
     * 再配置文件的最后一行之前插入新的配置
     */
    public static function appendBeforeLastline($data,$file)
    {
        $content = file_get_contents($file);

        $lines = explode("\n",$content);
        array_splice($lines,count($lines)-2,0,$data);
        $content  = implode("\n",$lines);
        echo 'content.........\n';

        var_dump($content);

        file_put_contents($file,$content);
    }
}
