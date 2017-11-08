<?php
/**
 * Opache相关清理
 *
 * @author  Terry (psr100)
 * @date    2017/10/11
 * @since   2017/10/11 18:06
 */

class OpcacheManager {
	/**
	 *  是否命令行模式
	 *
	 * @var
	 */
	private $isCliMode;

	/**
	 * 请求给OpacheManager的操作
	 *
	 * @var
	 */
	private $requestAction;

	/**
	 * 可用的action清单
	 *
	 * @var array
	 */
	private $availableActions = [
		'cleanAll',
		'printConfig',
		'printStatus',
	];

	/**
	 * OpcacheManager constructor.
	 */
	public function __construct()
	{
		$this->isCliMode = php_sapi_name() == 'cli';

		//该文件自身禁用opache缓存
		opcache_invalidate(__FILE__);

		$this->init();
	}

	/**
	 * 获取opcache操作参数
	 *
	 */
	private function init(){
		try {
			//扩展检测
			if (false == $this->opcacheEnabled()) {
				throw new Exception("Zend OPcache extension not set, You don't have to do anything!");
			}

			//基础参数检测
			if ($this->isCliMode) {
				$options = getopt('a:h', ['action:', 'help']);

				//帮助信息
				if (isset($options['h']) || isset($options['help']) || !isset($options['action'])) {
					throw new Exception('params error.');
				}

				$requestAction = $options['action'];

			} else {
				$requestAction = isset($_GET['action']) ? $_GET['action'] : '';
			}

			//参数可用性检测
			if ( !in_array($requestAction, $this->availableActions) ) {
				throw new Exception('error action !!');
			}

			$this->requestAction = $requestAction;
		}catch (Exception $exception) {
			$this->printHelpInfo($exception->getMessage());
		}

	}

	/**
	 * 预编译指定目录的所有php文件为字节码
	 *
	 * @param string $dir	待编译的路径
	 * @param array $files 递归存储引用
	 *
	 * @return array $files
	 */
	public function preCompileFiles($dir, &$files = []) {
		if (! is_dir($dir))
			return false;

		$dirObj = dir($dir);

		while (false !== ($file = $dirObj->read()) ) {
			if ($file == '.' || $file == '..') {
				continue;
			}

			//存储子文件
			$subFile = sprintf('%s%s%s', $dir, DIRECTORY_SEPARATOR, $file);
			if (false != @is_dir($subFile)) {
				$this->preCompileFiles($subFile, $files);
			}elseif(false != @is_file($subFile) && substr($subFile, -4) == '.php') {
				if ($this->preCompileFile($subFile)) {
					$files['success'] = $subFile;
				} else{
					$file['fail'] = $subFile;
				}
			}
		}

		return $files;
	}

	/**
	 * 预编译单个文件
	 *
	 * @param $file
	 * @return bool
	 */
	public function preCompileFile($file) {
		return opcache_compile_file($file);
	}

	/**
	 * opcache配置信息
	 *
	 * @return array
	 */
	public function getConfig() {
		return opcache_get_configuration();
	}

	/**
	 * opcache的状态
	 *
	 * @param bool $showScript
	 * @return array
	 */
	public function getStatus($showScript = false) {
		return opcache_get_status($showScript);
	}

	/**
	 * 清理文件的Opcache
	 *
	 * @param array|string $files
	 * @param bool $force 是否强制清除，否则会比较opcode与文件的新旧，默认在opcode落后情况才清除
	 * @return array
	 */
	public function cleanFilesOpcache($files = [], $force = false){
		$cleanRs = [];
		$files = is_array($files) ? $files : [$files];
		foreach ($files as $file) {
			if (opcache_invalidate($file, $force) == true) {
				$cleanRs['success'][] = $file;
			}else {
				$cleanRs['fail'][] = $file;
			}
		}
		return $cleanRs;
	}

	/**
	 * 重置所有的opcache
	 *
	 */
	public function cleanAllOpcache() {
		return opcache_reset();
	}

	/**
	 * 检测某个php文件是否已经生成了opcode
	 *
	 * @param $file 待检测的php文件
	 * @return bool
	 */
	public function isCached($file) {
		return opcache_is_script_cached($file);
	}

	/**
	 * 帮助信息
	 *
	 * @param string $errorMsg
	 */
	public function printHelpInfo($errorMsg) {
		$breakLine = $this->isCliMode ? "\r\n" : "<br/>";

		if ($this->isCliMode) {
			$helpList = [
				$errorMsg,
				"--------------------------------------------------",
				sprintf('Usage: php %s --action=cleanAll|printConfig|printStatus', basename(__FILE__)),
				'--action:',
				'  cleanAll 	: clean all opcache on server!',
				'  printConfig 	: print the opcache config!',
				'  printStatus 	: print the server opcache status!',
				"--------------------------------------------------",
			];
		} else {
			$helpList = [
				$errorMsg,
				"--------------------------------------------------",
				sprintf('Usage Request URI: %s/opcache.php?action==cleanAll|printConfig|printStatus', $_SERVER['REQUEST_URI']),
				'action:',
				'  cleanAll 	: clean all opcache on server!',
				'  printConfig 	: print the opcache config!',
				'  printStatus 	: print the server opcache status!',
				"--------------------------------------------------",
			];
		}

		echo join($breakLine, $helpList);
		exit(1);
	}

	/**
	 * 检测Zend Opcache是否已经加载
	 *
	 * @return bool
	 */
	public function opcacheEnabled() {
		return extension_loaded('Zend OPcache') && (ini_get('opcache.enable') == 1);
	}

	/**
	 * 运行opcacheManager
	 *
	 */
	public function run() {

		switch ($this->requestAction) {
			case 'cleanAll' : {
				var_dump($this->cleanAllOpcache());
				break;
			}

			case 'printConfig' : {
				var_dump($this->getConfig());
				break;
			}

			case 'printStatus' : {
				var_dump($this->getStatus(true));
				break;
			}
		}
	}

}

//预编译整个目录
(new OpcacheManager())->run();

