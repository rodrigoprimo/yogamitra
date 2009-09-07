<?php
/**
 * Retrieves and creates the wp-config.php file.
 *
 * The permissions for the base directory must allow for writing files in order
 * for the wp-config.php to be created using this page.
 *
 * @package WordPress
 * @subpackage Administration
 */

/**
 * We are installing.
 *
 * @package WordPress
 */
define('WP_INSTALLING', true);

/**#@+
 * These three defines are required to allow us to use require_wp_db() to load
 * the database class while being wp-content/db.php aware.
 * @ignore
 */
define('ABSPATH', dirname(dirname(__FILE__)).'/');
define('WPINC', 'wp-includes');
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
/**#@-*/

require_once(ABSPATH . WPINC . '/compat.php');
require_once(ABSPATH . WPINC . '/functions.php');
require_once(ABSPATH . WPINC . '/classes.php');

if (!file_exists(ABSPATH . 'wp-config-sample.php'))
	wp_die('É necessário que o arquivo wp-config-sample.php exista no seu servidor. Por favor re-envie este arquivo da instalação do WordPress.');

$configFile = file(ABSPATH . 'wp-config-sample.php');

if ( !is_writable(ABSPATH))
	wp_die("Não é possível gravar neste diretório. Você pode alterar as permissões do diretório WordPress ou criar seu arquivo wp-config.php manualmente.");

// Check if wp-config.php has been created
if (file_exists(ABSPATH . 'wp-config.php'))
	wp_die("<p>O arquivo 'wp-config.php' já existe. Se você precisa redefinir qualquer item de configuração deste arquivo, por favor apague-o primeiro. Você pode tentar <a href='install.php'>instalar agora</a>.</p>");

// Check if wp-config.php exists above the root directory but is not part of another install
if (file_exists(ABSPATH . '../wp-config.php') && ! file_exists(ABSPATH . '../wp-settings.php'))
	wp_die("<p>O arquivo 'wp-config.php' já existe um nível acima de sua instalação do WordPress. Se você precisa redefinir qualquer item de configuração deste arquivo, por favor apague-o primeiro. Você pode tentar <a href='install.php'>instalar agora</a>.</p>");
	
if (isset($_GET['step']))
	$step = $_GET['step'];
else
	$step = 0;

/**
 * Display setup wp-config.php file header.
 *
 * @ignore
 * @since 2.3.0
 * @package WordPress
 * @subpackage Installer_WP_Config
 */
function display_header() {
	header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>WordPress &rsaquo; Configuração do Arquivo de Instalação</title>
<link rel="stylesheet" href="css/install.css" type="text/css" />

</head>
<body>
<h1 id="logo"><img alt="WordPress" src="images/wordpress-logo.png" /></h1>
<?php
}//end function display_header();

switch($step) {
	case 0:
		display_header();
?>

<p>Bem-vindo ao Wordpress. Antes de começarmos precisamos de algumas informações do seu Banco de Dados. Você precisará saber os seguintes itens antes de prosseguir.</p>
<ol>
	<li>Nome do Banco de Dados</li>
	<li>Usuário do Banco de Dados</li>
	<li>Senha do Banco de Dados</li>
	<li>Servidor do Banco de Dados</li>
	<li>Prefixo das Tabelas (se você quiser rodar mais de um WordPress no mesmo Banco de Dados) </li>
</ol>
<p><strong>Se por alguma razão essa criação automática não funcionar, não se preocupe. Tudo que ela faz é inserir informações sobre o Banco de Dados no arquivo de configuração. Você pode também simplesmente abrir <code>wp-config-sample.php</code> em um editor de texto, adicionar as informações e salvar como <code>wp-config.php</code>.</strong></p>
<p>Na maioria dos casos estes itens são fornecidos pelo seu Serviço de Hospedagem. Se você não tem essas informações, então você precisa entrar em contato com eles antes de continuar. Se você estiver pronto&hellip;</p>

<p class="step"><a href="setup-config.php?step=1" class="button">Vamos começar!</a></p>
<?php
	break;

	case 1:
		display_header();
	?>
<form method="post" action="setup-config.php?step=2">
	<p>Abaixo você deve preencher os detalhes de conexão do seu Banco de Dados. Se você não tem certeza, entre em contato com seu provedor. </p>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="dbname">Nome do Banco de Dados</label></th>
			<td><input name="dbname" id="dbname" type="text" size="25" value="wordpress" /></td>
			<td>O nome do Banco de Dados que você deseja que o WP utilize. </td>
		</tr>
		<tr>
			<th scope="row"><label for="uname">Nome do Usuário</label></th>
			<td><input name="uname" id="uname" type="text" size="25" value="username" /></td>
			<td>Seu usuário do MySQL</td>
		</tr>
		<tr>
			<th scope="row"><label for="pwd">Senha</label></th>
			<td><input name="pwd" id="pwd" type="text" size="25" value="password" /></td>
			<td>...e a senha do MySQL.</td>
		</tr>
		<tr>
			<th scope="row"><label for="dbhost">Servidor do Banco de Dados</label></th>
			<td><input name="dbhost" id="dbhost" type="text" size="25" value="localhost" /></td>
			<td>Provavelmente você não precisará trocar este valor.</td>
		</tr>
		<tr>
			<th scope="row"><label for="prefix">Prefixo das Tabelas</label></th>
			<td><input name="prefix" id="prefix" type="text" id="prefix" value="wp_" size="25" /></td>
			<td>Se você quiser rodar múltiplas instalações de WordPress no mesmo Banco de Dados, altere esse campo.</td>
		</tr>
	</table>
	<p class="step"><input name="submit" type="submit" value="Enviar" class="button" /></p>
</form>
<?php
	break;

	case 2:
	$dbname  = trim($_POST['dbname']);
	$uname   = trim($_POST['uname']);
	$passwrd = trim($_POST['pwd']);
	$dbhost  = trim($_POST['dbhost']);
	$prefix  = trim($_POST['prefix']);
	if (empty($prefix)) $prefix = 'wp_';

	// Test the db connection.
	/**#@+
	 * @ignore
	 */
	define('DB_NAME', $dbname);
	define('DB_USER', $uname);
	define('DB_PASSWORD', $passwrd);
	define('DB_HOST', $dbhost);
	/**#@-*/

	// We'll fail here if the values are no good.
	require_wp_db();
	if ( !empty($wpdb->error) )
		wp_die($wpdb->error->get_error_message());

	$handle = fopen(ABSPATH . 'wp-config.php', 'w');

	foreach ($configFile as $line_num => $line) {
		switch (substr($line,0,16)) {
			case "define('DB_NAME'":
				fwrite($handle, str_replace("nomedoBD", $dbname, $line));
				break;
			case "define('DB_USER'":
				fwrite($handle, str_replace("'usuarioMySQL'", "'$uname'", $line));
				break;
			case "define('DB_PASSW":
				fwrite($handle, str_replace("'senha'", "'$passwrd'", $line));
				break;
			case "define('DB_HOST'":
				fwrite($handle, str_replace("localhost", $dbhost, $line));
				break;
			case '$table_prefix  =':
				fwrite($handle, str_replace('wp_', $prefix, $line));
				break;
			default:
				fwrite($handle, $line);
		}
	}
	fclose($handle);
	chmod(ABSPATH . 'wp-config.php', 0666);

	display_header();
?>
<p>Tudo certo! Você conseguiu terminar essa parte da instalação. O WordPress agora pode se comunicar com seu Banco de Dados. Se estiver pronto&hellip;</p>

<p class="step"><a href="install.php" class="button">Começar a instalação</a></p>
<?php
	break;
}
?>
</body>
</html>
