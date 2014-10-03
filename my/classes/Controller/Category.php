<?php defined('SYSPATH') or die('No direct script access.');
 
class Controller_Category extends Controller_Template {
 
    /**
     * @var View
     */
    //public $template = 'category/view';
 
    /**
     * View all tree action
     */
    public function action_view()
    {
    echo "asd";
        /** @var Model_Category $root **/ // check root category
        $root = ORM::factory('Category', array('lft' => 1, ));
 
        // if root not exist, create new root
        if (! $root->loaded())
        {
            // create new
            $root->name = 'Root';
            $root->description = 'Root node of categories tree';
            $root->save();
 
            // refresh root
            $root->reload();
        }
 
        // set template variables
        $article = View::factory('category/view');
        $article->root = $root;
        $article->categories = $root->get_descendants();
        $article->message = Session::instance()->get_once('message');
        $this->content = $article->render(); 
    }
 
    /**
     * Create node action
     */
    public function action_new()
    {
        $this->action_modify();
    }
 
    /**
     * Edit node action
     */
    public function action_edit()
    {
        $this->action_modify();
    }
 
    /**
     * Create or edit view action
     */
    private  function action_modify()
    {
        // change template
        $this->template = View::factory('category/modify');
 
        /** @var Model_Category $root **/ // check root category
        $root = ORM::factory('Category', array('lft' => 1, ));
 
        // check root
        if ( ! $root->loaded())
        {
            throw new HTTP_Exception_502('Root node of categories tree not founded');
        }
 
        /** @var Model_Category $node **/ // node
        $node = ORM::factory('Category', $this->request->param('id'));
 
        // set template variables
        $this->template->set(array(
            // all root node child's
            'root' => $root,
            'node' => $node,
            'categories' => $root->get_descendants(),
            'message' => Session::instance()->get_once('message'),
        ));
    }
 
    /**
     * Create, Update tree node action
     */
    public function action_save()
    {
        /** @var Model_Category $root **/ // root node
        $root = ORM::factory('Category', Arr::get($this->request->post(), 'parent'));
 
        // check root
        if ( ! $root->loaded())
        {
            throw new HTTP_Exception_502('Root node of categories tree not founded');
        }
 
        /** @var Model_Category $node **/ // create new node object
        $node = ORM::factory('Category', Arr::get($this->request->post(), 'id'));
 
        // bind data
        $node->values($this->request->post(), array('name', 'description'));
 
        // insert node as last child of root
        try {
            if (! $node->loaded())
            {
                // insert
                $node->insert_as_last_child_of($root);
            }
            else
            {
                // save
                $node->save();
 
                // change parent if needed
                if (! $root->is_equal_to($node->get_parent()))
                {
                    $node->move_as_last_child_of($root);
                }
            }
        } catch (Exception $e) {
            throw $e;
            // process error check
        }
 
        // setup success message
        Session::instance()->set('message', 'Operation was successfully completed');
 
        // redirect to modify page
        $this->request->redirect(Route::url('default', array('controller' => 'category', 'action' => 'edit', 'id' => $node->id)));
    }
 
    /**
     * Delete node action
     */
    public function action_delete()
    {
        /** @var Model_Category $node **/ // node to delete
        $node = ORM::factory('Category', $this->request->param('id'));
 
        // check node
        if (! $node->loaded())
        {
            // setup error message
            Session::instance()->set('message', 'Operation was failed');
 
            // redirect to view tree page
            $this->request->redirect('/');
        }
 
        // remove node
        $node->delete();
 
        // setup success message
        Session::instance()->set('message', 'Operation was successfully completed');
 
        // redirect to view tree page
        $this->request->redirect('/');
    }
 
} // End Controller Category