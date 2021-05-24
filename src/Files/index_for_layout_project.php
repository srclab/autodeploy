<?php /** @noinspection ALL */

namespace App;

use SrcLab\AutoDeploy\FrontendLayoutAutoDeploy;

require '../vendor/autoload.php';

/**
 * Автодеплой.
 */
FrontendLayoutAutoDeploy::process();

/**
 * В случае если автодеплой не потребовался, вывод дефолтного идексного файла.
 */
include "index.html";