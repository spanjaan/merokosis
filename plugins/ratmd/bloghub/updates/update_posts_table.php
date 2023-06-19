<?php

declare(strict_types=1);

namespace RatMD\BlogHub\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use System\Classes\PluginManager;

/**
 * CreateViewsTable Migration
 */
class CreateViewsTable extends Migration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        if (!PluginManager::instance()->hasPlugin('Winter.Blog')) {
            return;
        }

        Schema::table('winter_blog_posts', function (Blueprint $table) {
            $table->integer('ratmd_bloghub_views')->unsigned()->default(0);
            $table->integer('ratmd_bloghub_unique_views')->unsigned()->default(0);
        });
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        if (method_exists(Schema::class, 'dropColumns')) {
            Schema::dropColumns('winter_blog_posts', ['ratmd_bloghub_views', 'ratmd_bloghub_unique_views']);
        } else {
            Schema::table('winter_blog_posts', function (Blueprint $table) {
                if (Schema::hasColumn('winter_blog_posts', 'ratmd_bloghub_views')) {
                    $table->dropColumn('ratmd_bloghub_views');
                }
            });
            Schema::table('winter_blog_posts', function (Blueprint $table) {
                if (Schema::hasColumn('winter_blog_posts', 'ratmd_bloghub_unique_views')) {
                    $table->dropColumn('ratmd_bloghub_unique_views');
                }
            });
        }
    }
}
