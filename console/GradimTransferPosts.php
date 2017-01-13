<?php namespace Code200\Developer\Console;

use Bedard\BlogTags\Models\Tag;
use Code200\ImageKing\Classes\DomImageFinder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use League\Flysystem\Exception;
use October\Rain\Database\ModelException;
use PDO;
use PhpParser\Node\Expr\Empty_;
use RainLab\Blog\Models\Category;
use RainLab\Blog\Models\Post;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GradimTransferPosts extends Command
{

    const OLD_DB = "gradim_migration";
    const OLD_DB_USER = "root";
    const OLD_DB_PASS = "";

    /**
     * @var string The console command name.
     */
    protected $name = 'gradim:transfer-posts';

    /**
     * @var string The console command description.
     */
    protected $description = 'Transfers posts from old Joomla to OctoberCMS';

    /**
     * Execute the console command.
     * @return void
     */
    public function fire()
    {
        //connect to odl db connection
        $pdo = new PDO(
            'mysql:host=localhost;dbname=' . self::OLD_DB . ';charset=utf8',
            self::OLD_DB_USER,
            self::OLD_DB_PASS
        );

        $posts = $pdo->query("select * from jos_content");
        Post::where("id", ">", 2)->delete();
        Tag::where("id", ">", "-9")->delete();
        foreach ($posts as $post) {
//                print_r($post);
            try {

                $newPost = new Post();
                $this->info("Importing post: " . $post['title']);


                if (intval($post['state']) > 0) {
                    $newPost->published = 1;
                } else {
                    $this->comment("Post importing of: " . $post['title'] . " failed since it was not published!");
                    continue;
                }

                $newPost->title = $post['title'];
                $newPost->slug = $post['alias'];
                $newPost->excerpt = $this->cleanIntro($post['introtext']);
                if (empty($post['fulltext'])) {
                    $post['fulltext'] = $post['introtext'];
                }
                $newPost->content = $this->cleanContentText($post['fulltext']);
                $newPost->content_html = $this->cleanContentHtmlText($post['fulltext']);


                $newPost->created_at = $post['created'];
                if (!empty($post['created_by_alias'])) {
                    $newPost->author_alias = $post['created_by_alias'];
                }

                $newPost->updated_at = $post['modified'];
                $newPost->published_at = $post['publish_up'];
                $newPost->seo_description = $post['metadesc'];
                $newPost->seo_keywords = $post['metakey'];


                $category = Category::where("code", "c" . $post['catid'])->first();
                if (!empty($category)) {
                    $this->info("Importing post into category: " . $category->name);
                    $newPost->categories = [$category->id];
                }

                $tags =
                    array_filter(
                        array_map(function ($value) {
                            $value = str_slug(trim($value));
                            if (empty($value)) {
                                return;
                            }
                            //check if exists
                            $tag = Tag::where("name", "=", $value)->first();
                            if (!empty($tag)) {
                                return $tag->id;
                            }

                            $tag = new Tag();
                            //@todo check this - maybe add another field to blogtags plugin
                            $tag->name = $value;
                            $tag->save();

                            return $tag->id;


                        }, explode(",", $post['metakey'])),
                        function ($value) {
                            if (empty($value)) {
                                return false;
                            }

                            return true;
                        });


                $newPost->tags = $tags;

                $newPost->save();


                //metadescription


                //set category
                //find old category => "c".$post['catid']
//                $post->setCategory();
//                die;
            } catch (ModelException $e) {
                $this->error($e->getMessage());
                $this->error($e->getTraceAsString());
                if ($e->getMessage() == 'The slug has already been taken.') {
                    $this->error("The slug has already been taken.");
                }
            } catch (\ErrorException $f) {
                $this->error($f->getTraceAsString());
            }
        }

//        $this->output->writeln('Categories imported!');
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }


    private function cleanIntro($introText)
    {
        //clean imgs
//        $content = preg_replace("/<img[^>]+\>/i", "", $introText);
        $introText = strip_tags($introText, "<img>");
        $introText = str_replace("images/stories/", "/storage/app/media/", $introText);

//        $content = preg_replace("/</p>/i", "", $introText);
        return $introText;
    }

    private function cleanContentHtmlText($text) {
        $text = str_replace("images/stories/", "/storage/app/media/", $text);
        return $text;
    }


    private function cleanContentText($text) {
        $text = str_replace("images/stories/", "/storage/app/media/", $text);
        $text = str_replace("images/revije/", "/storage/app/media/revije/", $text);
        return $text;
    }
}
