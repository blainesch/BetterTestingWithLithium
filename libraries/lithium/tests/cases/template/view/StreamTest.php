<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace lithium\tests\cases\template\view;

use \lithium\template\view\Stream;

class StreamTest extends \lithium\test\Unit {

	protected $_path;

	public function setUp() {
		$this->_path = str_replace('\\', '/', LITHIUM_APP_PATH);
		file_put_contents($this->_path . '/resources/tmp/tests/template.html.php', "
			<?php echo 'this is unescaped content'; ?" . ">
			<?='this is escaped content'; ?" . ">
			<?=\$alsoEscaped; ?" . ">
			<?=\$this->escape('this is also escaped content'); ?" . ">
			<?=\$this->escape(
				'this, too, is escaped content'
			); ?" . ">
			<?='This is
				escaped content
				that breaks over
				several lines
			'; ?" . ">
		");
	}

	public function tearDown() {
		unlink($this->_path . '/resources/tmp/tests/template.html.php');
	}

	public function testPathFailure() {
		$stream = new Stream();
		$null = null;
		$result = $stream->stream_open(null, null, null, $null);
		$this->assertFalse($result);
	}

	public function testStreamContentRewriting() {
		$stream = new Stream();
		$null = null;
		$path = 'lithium.template://' . $this->_path . '/resources/tmp/tests/template.html.php';

		$stream->stream_open($path, null, null, $null);
		$result = array_map('trim', explode("\n", trim($stream->stream_read(999))));

		$expected = "<?php echo 'this is unescaped content'; ?" . ">";
		$this->assertEqual($expected, $result[0]);

		$expected = "<?php echo \$h('this is escaped content'); ?" . ">";
		$this->assertEqual($expected, $result[1]);

		$expected = "<?php echo \$h(\$alsoEscaped); ?" . ">";
		$this->assertEqual($expected, $result[2]);

		$expected = "<?php echo \$this->escape('this is also escaped content'); ?" . ">";
		$this->assertEqual($expected, $result[3]);

		$expected = '<?php echo $this->escape(';
		$this->assertEqual($expected, $result[4]);

		$expected = "'this, too, is escaped content'";
		$this->assertEqual($expected, $result[5]);

		$expected = '); ?>';
		$this->assertEqual($expected, $result[6]);

		$expected = "<?php echo \$h('This is";
		$this->assertEqual($expected, $result[7]);

		$expected = 'escaped content';
		$this->assertEqual($expected, $result[8]);

		$expected = 'that breaks over';
		$this->assertEqual($expected, $result[9]);

		$expected = 'several lines';
		$this->assertEqual($expected, $result[10]);

		$expected = "'); ?>";
		$this->assertEqual($expected, $result[11]);
	}
}

?>