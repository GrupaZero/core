<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Jobs\CreateFile;
use Gzero\Core\Models\Language;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileJobsTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    protected function _before()
    {
        Storage::fake('uploads');
    }

    /** @test */
    public function canCreateFile()
    {
        $user  = $this->tester->haveUser();
        $image = UploadedFile::fake()->image('avatar.jpg')->size(100);
        $file  = dispatch_now(CreateFile::image($image, 'New One', new Language(['code' => 'en']), $user, [
            'description' => 'description',
            'is_active'   => true,
        ]));

        $file        = $file->fresh();
        $translation = $file->translations->first();

        // Assert the file was stored...
        //Storage::disk('uploads')->assertExists('avatar.jpg');

        // Assert a file does not exist...
        Storage::disk('uploads')->assertMissing('avatar.jpg');

        $this->assertTrue($file->is_active);
        $this->assertEquals($user->id, $file->author->id);
        $this->assertEquals('avatar', $translation->language_code);
        $this->assertEquals('en', $translation->language_code);
    }
}

