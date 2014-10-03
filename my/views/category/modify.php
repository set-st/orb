<?php defined('SYSPATH') or die('No direct script access.');?><!DOCTYPE html>
<html>
    <head>
        <title>Categories</title>
        <style type="text/css">
            *{ margin: 0; padding: 0; }
            html, body{ width: 100%; height: 100%; }
            a:hover{ text-decoration: none; }
            #wrap{ margin: 0 auto; width: 960px; }
            #wrap h2{ margin: 20px 0; }
            #wrap .message{ padding: 5px; border: 3px solid #00f; color: #00f; margin-bottom: 20px; }
            #wrap .row{ margin-bottom: 5px; }
            #wrap .row label{ display: block; margin-bottom: 5px; }
            #wrap .row input, #wrap .row textarea, #wrap .row select{ width: 100%; }
            #wrap .row textarea{ height: 100px; resize: vertical; }
            #wrap .controls{ text-align: right }
        </style>
    </head>
 
    <body>
        <div id="wrap">
            <h2><?php echo $node->loaded() ? 'Edit' : 'New' ?> Category</h2>
            <?php if ($message) : ?>
            <div class="message"><?php echo HTML::chars($message) ?></div>
            <?php endif; ?>
            <form method="post" action="<?php echo Route::url('default', array('controller' => 'category', 'action' => 'save')) ?>">
                <div class="row">
                    <?php $parent = $node->get_parent() ?>
                    <label for="category_parent">Parent</label>
                    <select name="parent" id="category_parent">
                        <option value="<?php echo $root->id ?>">Root</option>
                        <?php if ($categories AND $categories->count() > 0) : ?>
                        <?php foreach ($categories as $category) : ?>
                            <option<?php if ($category->id == $parent->id) : ?> selected="selected"<?php endif ?> value="<?php echo $category->id ?>"><?php echo str_repeat(' ', $category->level * 3), HTML::chars($category->name) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="row">
                    <label for="category_name">Name</label>
                    <input type="text" name="name" value="<?php echo HTML::chars($node->name) ?>" id="category_name">
                </div>
                <div class="row">
                    <label for="category_description">Description</label>
                    <textarea rows="10" cols="10" name="description" id="category_description"><?php echo HTML::chars($node->description) ?></textarea>
                </div>
                <div class="controls">
                    <a href="/" title="Back to list">Back to list</a>
                    <input type="submit" value="<?php echo $node->loaded() ? 'Save' : 'Create' ?>">
                    <input type="hidden" name="id" value="<?php echo HTML::chars($node->id) ?>">
                </div>
            </form>
        </div>
    </body>
</html>