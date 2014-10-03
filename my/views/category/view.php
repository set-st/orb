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
            #wrap h2 a{ float: right; }
            #wrap .message{ padding: 5px; border: 3px solid #00f; color: #00f; margin-bottom: 20px; }
            #wrap table{ width: 100%; border-collapse: collapse; }
            #wrap table th, td{ border: 1px solid #000; padding: 5px; vertical-align: top; }
            #wrap table th{ background: #000; color: #fff; }
            #wrap .actions{ white-space: nowrap; width: 1%; }
        </style>
    </head>
 
    <body>
        <div id="wrap">
            <h2>Categories <a href="<?php echo Route::url('default', array('controller' => 'category', 'action' => 'new')) ?>" title="New">New</a></h2>
            <?php if ($message) : ?>
                <div class="message"><?php echo HTML::chars($message) ?></div>
            <?php endif; ?>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th class="actions">Actions</th>
                </tr>
                <?php if ($categories AND $categories->count() > 0) : ?>
                    <?php foreach($categories as $category) : /** @var Model_Category $category **/ ?>
                        <tr>
                            <td style="padding-left: <?php echo 5 + ($category->level - 1) * 10 ?>px"><?php echo HTML::chars($category->name) ?></td>
                            <td><?php echo HTML::chars($category->description) ?></td>
                            <td class="actions">
                                <a title="Edit" href="<?php echo Route::url('default', array('controller' => 'category', 'action' => 'edit', 'id' => $category->id)) ?>">Edit</a> |
                                <a title="Delete" href="<?php echo Route::url('default', array('controller' => 'category', 'action' => 'delete', 'id' => $category->id)) ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="3">Tree of categories is empty</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </body>
</html>