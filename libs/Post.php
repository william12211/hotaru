<?php
/**
 * Post functions
 *
 * PHP version 5
 *
 * LICENSE: Hotaru CMS is free software: you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 3 of 
 * the License, or (at your option) any later version. 
 *
 * Hotaru CMS is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 * FITNESS FOR A PARTICULAR PURPOSE. 
 *
 * You should have received a copy of the GNU General Public License along 
 * with Hotaru CMS. If not, see http://www.gnu.org/licenses/.
 * 
 * @category  Content Management System
 * @package   HotaruCMS
 * @author    Hotaru CMS Team
 * @copyright Copyright (c) 2009 - 2013, Hotaru CMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://www.hotarucms.org/
 */
namespace Libs;

class Post extends Prefab
{
	// individual posts
	protected $id = 0;
	protected $archived         = 'N';            // archived Yes or No (Y/N)
	protected $author           = 0;            // post author
        protected $authorname       = '';           // post authorname from user table on left join
        protected $email            = '';           // post author email from user table on left join
	protected $date             = '';           // post submission date
        protected $updatedts        = '';           // post updated date
	protected $pubDate          = '';           // post published date
	protected $status           = 'unsaved';    // initial status before database entry
	protected $type             = '';           // post type, e.g. news, blog, forum
	protected $category         = 1;            // default category 'all'
	protected $tags             = '';           // tags
	protected $title            = '';           // post title
	protected $origUrl          = '';           // original url for the submitted post
	protected $domain           = '';           // the domain of the submitted url
	protected $url              = '';           // post slug (needs SITEURL and category attached)
	protected $content          = '';           // post description
	protected $contentLength    = 50;           // default min characters for content
	protected $summary          = '';           // truncated post description
	protected $summaryLength    = 200;          // default max characters for summary
	protected $comments         ='open';        // is the comment form open or closed?
	protected $subscribe        = 0;            // is the post author subscribed to comments?
        protected $votesUp          = 0;            //
// JRB
        protected $lat          = 0.0;            //
        protected $lng          = 0.0;            //
	
	public $vars                = array();      // for additional fields
	
	/**
	 * Access modifier to set protected properties
	 */
	public function __set($var, $val)
	{
		$this->$var = $val;  
	}
	
	
	/**
	 * Access modifier to get protected properties
	 */
	public function &__get($var)
	{
		return $this->$var;
	}
	
	
	/**
	 * Get all the settings for the current post from an array of posts
	 *
	 * @param int $post_id - Optional row from the posts table in the database
	 * @param array $post_row - a post already fetched from the db, just needs reading
	 * @return bool
	 */    
	public function readPost($h, $post_id = 0, $post_row = NULL)
	{
                // Time test 2 Nov 2014
                // This method only taking 0.0002 to run each time
                // If run 20 times in a loop then total is 0.004
            
		$h->vars['post_error'] = false; 
		
		if (!$post_id && !$post_row) {
			$post_id = $this->id;   // use the id already assigned to $h->post
		}

		if ($post_id != 0) {
			$post_row = $h->getPost($post_id);
			if (!$post_row) { $h->vars['post_error'] = true; return false; }
		}

		if ($post_row && isset($post_row->post_id)) {
                    
                        // TODO do we need this in memory as well if we are mapping the post into its own object ?
                        $h->currentPost = $post_row;
                        // **** REMOVE above
                        
                        
                        
			$this->id = $post_row->post_id;
			$this->archived = $post_row->post_archived;
			$this->author = $post_row->post_author;
                        
                        if (isset($h->vars['currentUserVotedPosts'])) {
                            $this->userVoted = isset($h->vars['currentUserVotedPosts'][$this->id]) ? true : false;
                        } else {
                            $this->userVoted = isset($post_row->vote_rating) && $post_row->vote_rating> 0  ? true : false;
                        }
                        
                        $this->categoryName = isset($h->categoriesById[$post_row->post_category]->category_name) ? $h->categoriesById[$post_row->post_category]->category_name : '';
                        $this->categorySafeName = isset($h->categoriesById[$post_row->post_category]->category_safe_name) ? $h->categoriesById[$post_row->post_category]->category_safe_name : '';
                        
                        //print_r($post_row);
                        $this->authorname = isset($post_row->user->user_username) ? $post_row->user->user_username : null;
                        // TODO check this in query
                        $this->email = isset($post_row->user->user_email) ? $post_row->user->user_email : null;
                        
                        // Just until I fix the bookmarking list query to bring it over to models, use following as well
                        if (!$this->authorname) { $this->authorname = isset($post_row->user_username) ? $post_row->user_username : ''; }
                        if (!$this->email) { $this->email = isset($post_row->user_email) ? $post_row->user_email : ''; }
                        
                        $this->commentsCount = isset($post_row->post_comments_count) ? $post_row->post_comments_count : 0;
			$this->date = $post_row->post_date;
                        $this->updatedts = $post_row->post_updatedts;
			$this->pubDate = $post_row->post_pub_date;
			$this->status = $post_row->post_status;
			$this->type = urldecode($post_row->post_type);
			$this->category = urldecode($post_row->post_category);
			$this->tags = stripslashes(urldecode($post_row->post_tags));
			$this->title = stripslashes(urldecode($post_row->post_title));
			$this->origUrl = urldecode($post_row->post_orig_url);
			$this->domain = urldecode($post_row->post_domain);
			$this->url = urldecode($post_row->post_url);
                        $this->post_img = urldecode($post_row->post_img);
			$this->content = stripslashes(urldecode($post_row->post_content));
			$this->comments = $post_row->post_comments; // this is the comment box status not the number of comments
			$this->subscribe = $post_row->post_subscribe;
			
                        $this->votesUp = $post_row->post_votes_up;
                        
// JRB
//error_log( "POST" . var_dump( $post_row ));
                        $this->lat = floatval( $post_row->post_lat );
                        $this->lng = floatval( $post_row->post_lng );

                        $h->vars['votesUp'] = $this->votesUp; // only here for older sites using old votes plugin
                        
			$this->vars['post_row'] = $post_row;    // make available to plugins
			
			$h->pluginHook('post_read_post');                                                
                        
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Gets a single post from the database
	 *
	 * @param int $post_id - post id of the post to get
	 * @return array|false
	 */    
	public function getPost($h, $post_id = 0)
	{ 
                //$post = \Hotaru\Models\Post::getWithDetails($post_id);
                $post = \Hotaru\Models2\Post::getWithDetails($h, $post_id);
                
                if ($post) { return $post; } else { return false; }
	}
	
	
	/**
	 * Add a post to the database
	 *
	 * @return int $last_insert_id
	 */    
	public function addPost($h)
	{
//JRB
//error_log( "IN addPost: " . var_dump( $h ));
//error_log( "IN THIS: " . var_dump( $this ));
		$sql = "INSERT INTO " . TABLE_POSTS . " SET post_author = %d, post_date = CURRENT_TIMESTAMP, post_status = %s, post_type = %s, post_category = %d, post_tags = %s, post_title = %s, post_orig_url = %s, post_domain = %s, post_url = %s, post_content = %s, post_subscribe = %d, post_updateby = %d, post_lat = %s, post_lng = %s";

		
		$h->db->query($h->db->prepare($sql, $this->author, $this->status, urlencode($this->type), $this->category, urlencode(trim($this->tags)), urlencode(trim($this->title)), urlencode($this->origUrl), urlencode($this->domain), urlencode(trim($this->url)), urlencode(trim($this->content)), $this->subscribe, $h->currentUser->id, $this->lat, $this->lng));
		
		$last_insert_id = $h->db->get_var($h->db->prepare("SELECT LAST_INSERT_ID()"));
		
		$this->id = $last_insert_id;
		$this->vars['last_insert_id'] = $last_insert_id;    // make it available outside this class
		
		// Update post_date field if $this->date has been declared
		// Normally used when scheduling or auto-submitting posts
		if ($this->date) {
			$date = date('YmdHis', $this->date);
			$sql = "UPDATE " . TABLE_POSTS . " SET post_date = %s WHERE post_id = %d";
			$h->db->query($h->db->prepare($sql, $date, $last_insert_id));
		}
		
		// Add tags to the Tags table:
		//require_once(LIBS . 'Tags.php');
		$tags = new TagFunctions();
		$tags->addTags($h, $this->id, $this->tags);
		
		$h->pluginHook('post_add_post');
		
		return $last_insert_id;
	}
	
	
	/**
	 * Update a post in the database
	 *
	 * @return true
	 */    
	public function updatePost($h)
	{
		if (!$this->origUrl || strstr($this->origUrl, SITEURL)) {
			// original url contains our base url, so it must be an "editorial" post.
			// Therefore, it's essential we rebuild this source url to match the updated post title to avoid errors:
			$this->origUrl = $h->url(array('page'=>$this->id)); // update the url with the real one			
		}
		
		$parsed = parse_url($this->origUrl);
		if (isset($parsed['scheme'])){ $this->domain = $parsed['scheme'] . "://" . $parsed['host']; }
		
// JRB
		$sql = "UPDATE " . TABLE_POSTS . " SET post_author = %d, post_status = %s, post_type = %s, post_category = %d, post_tags = %s, post_title = %s, post_orig_url = %s, post_domain = %s, post_url = %s, post_content = %s, post_subscribe = %d, post_comments = %s, post_updateby = %d, post_lat = %s, post_lng = %s WHERE post_id = %d";
		
		$h->db->query($h->db->prepare($sql, $this->author, $this->status, urlencode($this->type), $this->category, urlencode(trim($this->tags)), urlencode(trim($this->title)), urlencode($this->origUrl), urlencode($this->domain), urlencode(trim($this->url)), urlencode(trim($this->content)), $this->subscribe, $this->comments, $h->currentUser->id, $this->lat, $this->lng, $this->id));
		
		$h->post->id = $this->id; // a small hack to get the id for use in plugins.
		
		// Update tags in the Tags table:		
		$tags = new TagFunctions();
		$tags->deleteTags($h, $this->id); // delete existing tags
		$tags->addTags($h, $this->id, $this->tags); // insert new or updated tags
		
		$h->pluginHook('post_update_post');
		
		return true;
	}
	
        /**
         * Update a post with image data
         * 
         * @param type $h
         * @param type $postId
         * @param type $img
         */
        public function imageUpdate($h, $postId, $img)
        {
            $sql = "UPDATE " . TABLE_POSTS . " SET post_img = %s WHERE post_id = %d";
            $h->db->query($h->db->prepare($sql, $img, $postId));
        }

        /**
	 * Physically delete a post from the database 
	 *
	 * There's a plugin hook in here to delete their parts, e.g. votes, coments, tags, etc.
	 */    
	public function deletePost($h)
	{
		if (!$this->id) { return false; }
		
		$sql = "DELETE FROM " . TABLE_POSTS . " WHERE post_id = %d";
		$h->db->query($h->db->prepare($sql, $this->id));
		
		$h->post->id = $this->id; // a small hack to get the id for use in plugins.
		
		// Delete tags from the Tags table:
		//require_once(LIBS . 'Tags.php');
		$tags = new TagFunctions();
		$tags->deleteTags($h, $this->id); // delete existing tags
		
		$h->pluginHook('post_delete_post');
		
		// Need to clear both these cache to be sure the post is removed from widgets:
		$h->clearCache('html_cache', false); 
		$h->clearCache('db_cache', false); 
	}
	
	
	/**
	 * Physically delete all posts by a specified user
	 *
	 * @param array $user_id
	 * @return bool
	 */
	public function deletePosts($h, $user_id = 0) 
	{
                // TODO we should be able to do the delete in 1 step without retrieving records first
		if (!$user_id) { return false; }

                //$results = \Hotaru\Models\Post::getByAuthor($user_id);
                $results = \Hotaru\Models2\Post::getByAuthor($h, $user_id);
				
		if ($results) {
			foreach ($results as $r) {
				$h->post->id = $r->post_id; // used by other plugins in "post_delete_post" function/hook
				$this->deletePost($h);
			}
		}
		
		return true;
	}
	
	
	/**
	 * Update a post's status
	 *
	 * @param string $status
	 * @param int $post_id (optional)
	 * @return true
	 */    
	public function changePostStatus($h, $status = "processing", $post_id = 0)
	{
		$this->status = $status;
		if (!$post_id) { $post_id = $this->id; }
		    
		$sql = "UPDATE " . TABLE_POSTS . " SET post_status = %s WHERE post_id = %d";
		$h->db->query($h->db->prepare($sql, $this->status, $post_id));
		
		// hacks for plugins:
		$h->post->id = $post_id;
		 
		$h->pluginHook('post_change_status');
		        
		return true;
	}
	
	
	/**
	 * Checks for existence of a source url (in social bookmarking)
	 *
	 * @return array|false - array of exitsing posts
	 */    
	public function urlExists($h, $url = '')
	{
                //$posts = \Hotaru\Models\Post::getPostsByOrigUrl(urlencode($url));
                $posts = \Hotaru\Models2\Post::getPostsByOrigUrl($h, urlencode($url));
                
		if (!$posts) { return false; }
		
		// check whether any of found posts are being processed, if so let's delete them:
		foreach ($posts as $post) {
			if ($post->post_status == 'processing') {
				$h->post->id = $post->post_id;
				$h->deletePost($h);
			}
		}
		
		// check again to see if url is still present:
                //$post = \Hotaru\Models\Post::getFirstPostByOrigUrl(urlencode($url));
                $post = \Hotaru\Models2\Post::getFirstPostByOrigUrl($h, urlencode($url));
                		
		// if present return the first existing row
		if ($post) { return $post; } else { return false; }
	}
	
	
	/**
	 * Checks for existence of a post title
	 *
	 * @param str $title
	 * @return int - id of post with matching title
	 */
	public function titleExists($h, $title = '')
	{
		$title = trim($title);
		if (!$title) { return FALSE; }

                //$posts = \Hotaru\Models\Post::getPostsByTitle(urlencode($title));
                $posts = \Hotaru\Models2\Post::getPostsByTitle($h, urlencode($title));
				
		if (!$posts) { return false; }
		
		// we know there's at least one post with the same title, so if it's processing, let's delete it:
		foreach ($posts as $post) {
			if ($post->post_status == 'processing') {
				$h->post->id = $post->post_id;
				$h->deletePost($h);
			}
		}
		
		// One last check to see if a post is present:
                //$post_id = \Hotaru\Models\Post::getFirstPostByTitle(urlencode($title));
                $post_id = \Hotaru\Models2\Post::getFirstPostByTitle($h, urlencode($title));
		
		if ($post_id) { return $post_id; } else { return false; }
	}
	
	
	/**
	 * Checks for existence of a post with given post_url
	 *
	 * @param str $post_url (slug)
	 * @return int - id of post with matching url
	 */
	public function isPostUrl($h, $url = '')
	{
                //$post_id = \Hotaru\Models\Post::getFirstPostByPostUrl(urlencode($url));
                $post_id = \Hotaru\Models2\Post::getFirstPostByPostUrl($h, urlencode($url));
                if ($post_id) { return $post_id; } else { return false; }
	}
	
	
        /**
         * Get the flags for this post
         * 
         * @param type $h
         * @param type $post_id
         * @return type
         */
        public function getFlags($h, $post_id = 0, $raw = 0)
        {
            if ($post_id == 0) {
                $post_id = isset($h->post->id) ? $h->post->id : 0;
            }
            
            if ($post_id == 0) { return null; }
            
            $sql = "SELECT * FROM " . TABLE_POSTVOTES . " WHERE vote_post_id = %d AND vote_rating = %d";
            $flagged = $h->db->get_results($h->db->prepare($sql, $h->post->id, -999));   
            
            if ($raw) {
                return $flagged;
            }
            
            $flaggedArray = array();
            if ($flagged) {                
                foreach ($flagged as $flag) {
                    array_push($flaggedArray, $flag->vote_reason);                    
                }
            }
            
            return $flaggedArray;
        }
        
        
	/**
	 * Count how many approved posts a user has had
	 *
	 * @param int $userid (optional)
	 * @param int $post_type (optional)
	 * @return int 
	 */
	public function postsApproved($h, $user_id = 0, $post_type = 'news')
	{
		if (!$user_id) { $user_id = $h->currentUser->id; }
		
                $sql = "SELECT COUNT(post_id) FROM " . TABLE_POSTS . " WHERE (post_status = %s || post_status = %s) AND post_author = %d AND post_type = %s";                
                $count = $h->db->get_var($h->db->prepare($sql, 'top', 'new', $user_id, $post_type));
                
		return $count;	
	}
	
	
	/**
	 * Delete posts with "processing" status that are older than 30 minutes
	 */
	public function deleteProcessingPosts($h)
	{
		$sql = 'SELECT NOW();'; // use mysql time
		$timestamp = strtotime($h->db->get_var($sql));
		$exp = date('YmdHis', $timestamp - (60 * 30));
		$sql = "DELETE FROM " . TABLE_POSTS . " WHERE post_status = %s AND post_date < %s";
		
                $h->db->query($h->db->prepare($sql, 'processing', $exp));
	}
	
	
	/**
	 * Count posts in the last X hours/minutes for this user
	 *
	 * @param int $hours
	 * @param int $minutes
	 * @param int $user_id (optional)
	 * @param int $post_type (optional)
	 * @return int 
	 */
	public function countPosts($h, $hours = 0, $minutes = 0, $user_id = 0, $post_type = 'news')
	{
		if (!$user_id) { $user_id = $h->currentUser->id; }
		if ($hours) { 
			$time_ago = "-" . $hours . " Hours";
		} else {
			$time_ago = "-" . $minutes . " minutes";
		} 
		
		$start = date('YmdHis', time_block());
		$end = date('YmdHis', strtotime($time_ago));
		$sql = "SELECT COUNT(post_id) FROM " . TABLE_POSTS . " WHERE post_archived = %s AND post_author = %d AND post_type = %s AND (post_date >= %s AND post_date <= %s)";
		$count = $h->db->get_var($h->db->prepare($sql, 'N', $user_id, $post_type, $end, $start));
		
		return $count;
	}
        
        
        /**
	 * Count posts in the last X hours/minutes for this user
	 *
	 * @param int $hours
	 * @param int $minutes
	 * @param int $user_id (optional)
	 * @param int $post_type (optional)
	 * @return int 
	 */
	public function countPostsFilter($h, $hours = 0, $minutes = 0, $filter = '', $filterText = '', $link = '', $post_type = 'news')
	{		
		if ($hours) { 
			$time_ago = "-" . $hours . " Hours";
		} else {
			$time_ago = "-" . $minutes . " minutes";
		} 
                
                $and = '';
                if ($filter == 'tag') $and = ' AND post_tags = %s';
                elseif ($filter == 'category') $and = ' AND post_category = %s';
		else { $and = ' AND 1= %d'; $filterText = 1; }                               
                    
		$start = date('YmdHis', time_block());
		$end = date('YmdHis', strtotime($time_ago));
		$sql = "SELECT COUNT(post_id) FROM " . TABLE_POSTS . " WHERE post_archived = %s" . $and . ' AND post_type = %s AND post_status <> %s'; // . " AND (post_date >= %s AND post_date <= %s)";
		$count = $h->db->get_var($h->db->prepare($sql, 'N', $filterText, $post_type, 'pending')); //, $end, $start));
		
		return $count;
	}
	
	
	/**
	 * Get Unique Post Statuses
	 *
	 * @return array|false
	 */
	public function getUniqueStatuses($h) 
	{
		/* This function pulls all the different statuses from current links, 
		or adds some defaults if not present.*/
		
		$unique_statuses = array();
		
		// Some essentials:
		array_push($unique_statuses, 'new');
		array_push($unique_statuses, 'top');
		array_push($unique_statuses, 'pending');
		array_push($unique_statuses, 'buried');
		array_push($unique_statuses, 'processing');
		
		// Add any other statuses already in use:
		$sql = "SELECT DISTINCT post_status FROM " . TABLE_POSTS;
		$statuses = $h->db->get_results($h->db->prepare($sql));
		if ($statuses) {
			foreach ($statuses as $status) {
				if ($status->post_status && !in_array($status->post_status, $unique_statuses)) {
					array_push($unique_statuses, $status->post_status);
				}
			}
		}
		
		if ($unique_statuses) { return $unique_statuses; } else { return false; }
	}
	
	
	/**
	 * Post stats
	 *
	 * @return array
	 */
	public function stats($h, $stat_type = '')
	{
		switch ($stat_type) {
		    default:
			$sql = "SELECT post_status, count(post_id) FROM " . TABLE_POSTS . " GROUP BY post_status";
			$query = $h->db->prepare($sql);
			$h->smartCache('on', 'posts', 60, $query); // start using cache
			$posts = $h->db->get_results($query, ARRAY_N);
			break;
		case 'archived':
			$sql = "SELECT count(post_id) FROM " . TABLE_POSTS . " WHERE post_archived = %s";
			$query = $h->db->prepare($sql, 'Y');
			$h->smartCache('on', 'posts', 60, $query); // start using cache
			$posts = $h->db->get_var($query);
			break;
                case 'total':
                        $sql = "SELECT count(post_id) FROM " . TABLE_POSTS . " WHERE post_status <> %s AND post_archived = %s";
                        $query = $h->db->prepare($sql, 'pending', 'N');                        
                        $h->smartCache('on', 'posts', 60, $query); // start using cache
                        $posts = $h->db->get_var($query);
                        break;
		case 'totalweek':
			$end = date('Y-m-d', strtotime('last Sunday', time()));
			$sql = "SELECT count(post_id) FROM " . TABLE_POSTS . " WHERE post_status <> %s AND post_archived = %s AND post_date >= %s";
                        $query = $h->db->prepare($sql, 'pending', 'N', $end);                        
                        $h->smartCache('on', 'posts', 60, $query); // start using cache
                        $posts = $h->db->get_var($query);						
			break;
		}	

		$h->smartCache('off'); // stop using cache

		return $posts;
	}
}
