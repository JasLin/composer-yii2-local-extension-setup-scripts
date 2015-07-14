
Composer-yii2-local-extension-setup-scripts
-------------------------------------------

this script with help to generate local yii2 extension config of yii2 project


### root pakcage composer.json sample 

your composer.json of your yii2 project should look like :

```
{
  "config" : {
    "vendor-dir" : "vendor"
  },
  "require": {
      "jaslin/composer-yii2-local-extension-setup-scripts": "dev-master"
  },
  "scripts" : {
      "post-autoload-dump" : "jaslin\\yii2\\composer\\ComposerScripts::postAutoloadDump"
  },
  "extra" : {
      "local-psr-4" : [
        "    'botwave\\\\rbac\\\\' => array($vendorDir . '/botwave/rbac'),",
        "    'botwave\\\\user\\\\' => array($vendorDir .'/botwave/user'),"
      ],
      "local-yii2-extensions" : [
        {
            "name" : "botwave/cms",
            "version" : "dev-master",
            "alias" : "@botwave/cms",
            "path" : "$vendor.'/botwave/cms'"
        },
        {
            "name" : "botwave/message",
            "version" : "9999999-dev",
            "alias" : "@botwave/message",
            "path" : "$vendor.'/botwave/message'"
        }
      ] 
  }
}

```

### result

this scripts will generate  psr-4.php(for composer) and extensions.php (for yii2) with the configuration from the root package composer.json
it will append the new configuration to the original config files.
