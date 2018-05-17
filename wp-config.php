<?php
/**
 * WordPress基础配置文件。
 *
 * 这个文件被安装程序用于自动生成wp-config.php配置文件，
 * 您可以不使用网站，您需要手动复制这个文件，
 * 并重命名为“wp-config.php”，然后填入相关信息。
 *
 * 本文件包含以下配置选项：
 *
 * * MySQL设置
 * * 密钥
 * * 数据库表名前缀
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/zh-cn:%E7%BC%96%E8%BE%91_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL 设置 - 具体信息来自您正在使用的主机 ** //
/** WordPress数据库的名称 */
define('DB_NAME', $_ENV['MYSQL_DBNAME']);

/** MySQL数据库用户名 */
define('DB_USER', 	$_ENV['MYSQL_USERNAME']);

/** MySQL数据库密码 */
define('DB_PASSWORD', $_ENV['MYSQL_PASSWORD']);

/** MySQL主机 */
define('DB_HOST',$_ENV['MYSQL_HOST']);

/** 创建数据表时默认的文字编码 */
define('DB_CHARSET', 'utf8mb4');

/** 数据库整理类型。如不确定请勿更改 */
define('DB_COLLATE', '');

/**#@+
 * 身份认证密钥与盐。
 *
 * 修改为任意独一无二的字串！
 * 或者直接访问{@link https://api.wordpress.org/secret-key/1.1/salt/
 * WordPress.org密钥生成服务}
 * 任何修改都会导致所有cookies失效，所有用户将必须重新登录。
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'x,}^p}Z]rq1;]4c(ZP8dmEY?z^F;^:Wgi2x*D.nKo:O^_VWr)bXfEwF^={i(?34v');
define('SECURE_AUTH_KEY',  '1!a}&=e-Tc$if3NxZnQ4RZsqER%`&+X7!1t?]<{ZPGqi>`BSPhfeur;[Val]Q0@%');
define('LOGGED_IN_KEY',    'VhfJC[$.uq2=@*./Bf77:5B26J+f>loHea>%uGG}Di{{o/zpj+j|qCaH_[1|14,L');
define('NONCE_KEY',        '$0M}{Ghm^e!J7rU@T6P!b_q)k/v!mTA S9N?f[ PH]3Y*w;q%87Ya^wv,kq[pZEy');
define('AUTH_SALT',        '(u6E0c)f4`N_~@]4c;@7)I}m*4WT&`)2[M(JDR-Q.#gQF.[Ac7!/2B!MELvp5JuV');
define('SECURE_AUTH_SALT', 'H_KtzFjZWb}5dd4MH53F_1i>5l*ut&X;yjpclblIz2]oPs)>&Qds3r<[oGV-%gLg');
define('LOGGED_IN_SALT',   '#$BK#K;a`&>Oahmd_|6(/)HVKc3L`/*:Ic`-YZ];>ima!Hr0V{9_iv9758gQ!F;1');
define('NONCE_SALT',       '8  M(%i.|RuI<R@%o{QxA:(!5ReWV&V:=`{i+}dxvosd_P8NG4=5,0sk(6tSHJ6E');

/**#@-*/

/**
 * WordPress数据表前缀。
 *
 * 如果您有在同一数据库内安装多个WordPress的需求，请为每个WordPress设置
 * 不同的数据表前缀。前缀名只能为数字、字母加下划线。
 */
$table_prefix  = 'wp_';

/**
 * 开发者专用：WordPress调试模式。
 *
 * 将这个值改为true，WordPress将显示所有用于开发的提示。
 * 强烈建议插件开发者在开发环境中启用WP_DEBUG。
 *
 * 要获取其他能用于调试的信息，请访问Codex。
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/**
 * zh_CN本地化设置：启用ICP备案号显示
 *
 * 可在设置→常规中修改。
 * 如需禁用，请移除或注释掉本行。
 */
define('WP_ZH_CN_ICP_NUM', true);

/* 好了！请不要再继续编辑。请保存本文件。使用愉快！ */

/** WordPress目录的绝对路径。 */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** 设置WordPress变量和包含文件。 */
require_once(ABSPATH . 'wp-settings.php');
