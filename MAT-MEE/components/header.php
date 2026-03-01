<?php

//------------------------------------------ Header Component ------------------------------------------ 

require_once 'config-component.php';

?>



<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar navbar-expand-sm navbar-light">
            <div class="container">
                <!-- Logo Left -->
                <a class="navbar-brand" href="#">
                    <div class="logo-wrapper">
                        <img src="image/logo.png" alt="MAT MEE" class="logo">
                        <span class="brand-name">MAT MEE</span>
                    </div>
                </a>
                
                <!-- Toggler -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <!-- Menu -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <?php include 'menu.php'; ?>
                </div>
            </div>
        </nav>
    </header>