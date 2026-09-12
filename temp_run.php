$<?php
chdir(__DIR__ . "/frontend");
session_start();
$_SESSION["user_id"] = 1;
$_GET["level"] = 1;
$_GET["topic"] = "ENGLISH.yaml";
include "lessonexam.php";

