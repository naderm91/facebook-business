<?php

unset($_SESSION['fb_business']);

header("Location: " . $_ENV['APP_URL']);
