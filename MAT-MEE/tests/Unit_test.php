<?php

session_start();
if (!isset($_SESSION['test_access'])) {
        header('Location: login.php');
        exit();
}

?>
<style>
body,a{
    background:#000;
    color:#fff;
    font-family:monospace;
    padding:10px 10px;
}
::selection, .inv{
    background:#fff;
    color:#000;
}
img{
    height:12vh;
}
</style>
<?php

class UnitTest {
    public function __construct(string $testname) {
        echo "";
        echo "<h1>Unit Test - " . $testname . "</h1>";
        echo "------------------------------------------------------------------------------";
        echo "<span'><a href='logout.php'>Logout</a></span>";
    }
    public static function ok ($message) {
        echo '<p> OK = ' . $message . '</p>';
    }
    public static function allOk() {
        echo '<p>All tests completed.</p>';
    }
    public static function assertEquals($expected, $actual, $message = '') {
        if ($expected !== $actual) {
            echo "Assertion Failed: " . ($message ?: "Expected '$expected' but got '$actual'") . "\n";
        } else {
            echo "Assertion Passed: " . ($message ?: "Expected and got '$expected'") . "\n";
        }
    }
    public static function assertTrue($condition, $message = '') {
        if (!$condition) {
            echo "Assertion Failed: " . ($message ?: "Condition is not true") . "\n";
        } else {
            echo "Assertion Passed: " . ($message ?: "Condition is true") . "\n";
        }
    }
    public static function echo ($message) {
        echo '<p>' . $message . '</p>';
    }
    public static function hr(){
        echo "------------------------------------------------------------------------------------";
    }
    public static function title($title){
        echo '<br><h2>'.$title.'</h2>';
        self::hr();
    }
}

?>