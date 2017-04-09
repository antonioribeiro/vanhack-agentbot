<?php

use App\Data\Entities\ChatType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_types', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name');
            $table->string('slug');

            $table->timestamps();
        });

        ChatType::create([
            'name' => 'Facebook Messenger',
            'slug' => 'facebook_messenger',
        ]);

        ChatType::create([
            'name' => 'Telegram',
            'slug' => 'telegram',
        ]);

        ChatType::create([
            'name' => 'Slack',
            'slug' => 'slack',
        ]);

        ChatType::create([
            'name' => 'HipChat',
            'slug' => 'hipchat',
        ]);

        ChatType::create([
            'name' => 'Nexmo',
            'slug' => 'nexmo',
        ]);

        ChatType::create([
            'name' => 'WeChat',
            'slug' => 'wechat',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_types');
    }
}
