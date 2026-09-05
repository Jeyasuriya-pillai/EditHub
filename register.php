<?php
// Registration now lives on the login page (tabbed). Redirect there.
header("Location: login.php?action=signup");
exit();