<?php

declare(strict_types=1);

namespace RatMD\BlogHub\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use System\Classes\PluginManager;

/**
 * UpdateBackendUsers Migration
 */
class UpdateBackendUsersTable extends Migration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        Schema::table('backend_users', function (Blueprint $table) {
            $table->string('ratmd_bloghub_display_name', 128)->nullable();
            $table->string('ratmd_bloghub_author_slug', 128)->unique()->nullable();
            $table->text('ratmd_bloghub_about_me')->nullable();
        });
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        if (method_exists(Schema::class, 'dropColumns')) {
            Schema::dropColumns('backend_users', ['ratmd_bloghub_display_name', 'ratmd_bloghub_author_slug', 'ratmd_bloghub_about_me']);
        } else {
            Schema::table('backend_users', function (Blueprint $table) {
                if (Schema::hasColumn('backend_users', 'ratmd_bloghub_display_name')) {
                    $table->dropColumn('ratmd_bloghub_display_name');
                }
                if (Schema::hasColumn('backend_users', 'ratmd_bloghub_author_slug')) {
                    $table->dropColumn('ratmd_bloghub_author_slug');
                }
                if (Schema::hasColumn('backend_users', 'ratmd_bloghub_about_me')) {
                    $table->dropColumn('ratmd_bloghub_about_me');
                }
            });
        }
    }
}
