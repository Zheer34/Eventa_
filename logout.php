<?php
session_start();
session_unset();
session_destroy();
header("Location: SignUp_LogIn_Form.html");
exit();
?>
