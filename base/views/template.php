<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?=$title;?></title>
    <link rel="stylesheet" href="<?=CDN?>/css/foundation.css" />
    <link rel="stylesheet" href="<?=PUBL?>/css/site.css" />
    <script src="<?=CDN?>/js/vendor/modernizr.js"></script>
</head>
<body>

<div class="row">
    <div class="large-12 columns">
        <nav class="top-bar" data-topbar>
            <ul class="title-area">
                <li class="name">
                    <h1><a href="/"><?=$title;?></a></h1>
                </li>
                <!-- Remove the class "menu-icon" to get rid of menu icon. Take out "Menu" to just have icon alone -->
                <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
            </ul>

            <section class="top-bar-section">
                <!-- Right Nav Section -->
                <ul class="right">
                    <li class="active"><a href="#">Right Button Active</a></li>
                    <li class="has-dropdown">
                        <a href="#">Right Button Dropdown</a>
                        <ul class="dropdown">
                            <li><a href="#">First link in dropdown</a></li>
                        </ul>
                    </li>
                </ul>

                <!-- Left Nav Section -->
                <ul class="left">
                    <li><a href="#">Left Nav Button</a></li>
                </ul>
            </section>
        </nav>
    </div>
</div>

<div class="row spacer"></div>

<div class="row">
    <div class="large-12 columns">
        <div class="row">
            <div class="large-9 columns">
                <?php echo $content; ?>
            </div>
            <div class="large-3 columns">
                <?=Widget::factory('Calendar_Base',array('a','b'));?>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="large-12 columns">
        <div>Footer</div>
    </div>
</div>

<script src="<?=CDN?>/js/vendor/jquery.js"></script>
<script src="<?=CDN?>/js/foundation.min.js"></script>
<script>
    $(document).foundation();
</script>
</body>
</html>