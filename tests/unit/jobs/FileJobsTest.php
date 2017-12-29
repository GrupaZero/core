<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Jobs\AddFileTranslation;
use Gzero\Core\Jobs\CreateFile;
use Gzero\Core\Jobs\DeleteFile;
use Gzero\Core\Jobs\DeleteFileTranslation;
use Gzero\Core\Jobs\SyncFiles;
use Gzero\Core\Jobs\UpdateFile;
use Gzero\Core\Models\File;
use Gzero\Core\Models\FileTranslation;
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
        $image = UploadedFile::fake()->image('file.jpg')->size(10);

        $file = dispatch_now(CreateFile::image($image, 'Image', new Language(['code' => 'en']), $user, [
            'info'        => 'info',
            'description' => 'My image',
            'is_active'   => true,
        ]));

        $file        = File::find($file->id);
        $translation = $file->translations->firstWhere('language_code', 'en');

        Storage::disk('uploads')->assertExists('images/file.jpg');

        $this->assertTrue($file->is_active);
        $this->assertEquals($user->id, $file->author->id);
        $this->assertEquals('file', $file->name);
        $this->assertEquals('jpg', $file->extension);
        $this->assertEquals(10240, $file->size);
        $this->assertEquals('image/jpeg', $file->mime_type);
        $this->assertEquals('info', $file->info);
        $this->assertEquals('image', $file->type->name);
        $this->assertEquals('en', $translation->language_code);
        $this->assertEquals('Image', $translation->title);
        $this->assertEquals('My image', $translation->description);
    }

    /** @test */
    public function canCreateFileWithUniqueName()
    {
        $user  = $this->tester->haveUser();
        $image = UploadedFile::fake()->image('file.jpg')->size(10);

        dispatch_now(CreateFile::image($image, 'Image', new Language(['code' => 'en']), $user));

        $file = dispatch_now(CreateFile::image($image, 'Another image', new Language(['code' => 'en']), $user, [
            'description' => 'My another image',
            'is_active'   => true,
        ]));

        $newName     = $file->name;
        $file        = File::find($file->id);
        $translation = $file->translations->firstWhere('language_code', 'en');

        Storage::disk('uploads')->assertExists('images/file.jpg');
        Storage::disk('uploads')->assertExists($file->getFullPath());

        $this->assertTrue($file->is_active);
        $this->assertEquals($user->id, $file->author->id);
        $this->assertEquals($newName, $file->name);
        $this->assertEquals('en', $translation->language_code);
        $this->assertEquals('Another image', $translation->title);
        $this->assertEquals('My another image', $translation->description);
    }

    /** @test */
    public function canUpdateFile()
    {
        $file = factory(File::class)->create(['type' => 'image', 'is_active' => true, 'info' => 'test']);

        dispatch_now(new UpdateFile($file, [
            'info'      => 'Updated info',
            'is_active' => false,
        ]));

        $file = File::find($file->id);

        $this->assertFalse($file->is_active);
        $this->assertEquals('Updated info', $file->info);;
    }

    /** @test */
    public function canDeleteFile()
    {
        $user  = $this->tester->haveUser();
        $image = UploadedFile::fake()->image('file.jpg')->size(10);

        $file = dispatch_now(CreateFile::image($image, 'Another image', new Language(['code' => 'en']), $user, [
            'description' => 'My another image',
            'is_active'   => true,
        ]));

        dispatch_now(new DeleteFile($file));

        Storage::disk('uploads')->assertMissing('images/file.jpg');
        $this->assertNull(File::find($file->id));
    }

    /** @test */
    public function canAddFileTranslation()
    {
        $user     = $this->tester->haveUser();
        $language = new Language(['code' => 'en']);
        $file     = factory(File::class)->create(['type' => 'image', 'is_active' => true, 'info' => 'test']);

        $this->assertEquals(0, $file->translations()->count());

        $translation = dispatch_now(new AddFileTranslation($file, 'New translation', $language, $user,
            ['description' => 'Description']
        ));

        $translation = FileTranslation::find($translation->id);

        $this->assertEquals(1, $file->translations()->count());
        $this->assertEquals($user->id, $translation->author->id);
        $this->assertEquals('New translation', $translation->title);
        $this->assertEquals('Description', $translation->description);
        $this->assertEquals($language->code, $translation->language_code);
    }

    /** @test */
    public function shouldRemoveExistingFileTranslationWhenAddingNewOneInSpecifiedLanguage()
    {
        $user     = $this->tester->haveUser();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('file.jpg')->size(10);

        $file = dispatch_now(CreateFile::image($image, 'Image', $language, $user, [
            'info'        => 'info',
            'description' => 'My image',
            'is_active'   => true,
        ]));

        $this->assertEquals(1, $file->translations()->count());

        $translation = dispatch_now(new AddFileTranslation($file, 'New translation', $language, $user,
            ['description' => 'Description']
        ));

        $translation = FileTranslation::find($translation->id);

        $this->assertEquals(1, $file->translations()->count());
        $this->assertEquals($user->id, $translation->author->id);
        $this->assertEquals('New translation', $translation->title);
        $this->assertEquals('Description', $translation->description);
        $this->assertEquals($language->code, $translation->language_code);
    }

    /** @test */
    public function canDeleteFileTranslation()
    {
        $user  = $this->tester->haveUser();
        $image = UploadedFile::fake()->image('file.jpg')->size(10);

        $file = dispatch_now(CreateFile::image($image, 'Image', new Language(['code' => 'en']), $user, [
            'info'        => 'info',
            'description' => 'My image',
            'is_active'   => true,
        ]));

        $this->assertEquals(1, $file->translations()->count());

        dispatch_now(new DeleteFileTranslation($file->translations()->first()));

        $this->assertEquals(0, $file->translations()->count());
    }

    /** @test */
    public function canSyncFiles()
    {
        $user   = $this->tester->haveUser();
        $entity = $this->tester->haveUploadableEntity();
        $image  = UploadedFile::fake()->image('file.jpg')->size(10);

        $file = dispatch_now(CreateFile::image($image, 'Image', new Language(['code' => 'en']), $user, [
            'info'        => 'info',
            'description' => 'My image',
            'is_active'   => true,
        ]));

        dispatch_now(new SyncFiles($entity, [$file->id => ['weight' => 3]]));

        $this->assertEquals(1, $entity->getSyncedFiles()->count());
    }

    /** @test */
    public function shouldSyncOnlyExistingFiles()
    {
        $user   = $this->tester->haveUser();
        $entity = $this->tester->haveUploadableEntity();
        $image  = UploadedFile::fake()->image('file.jpg')->size(10);

        $file  = dispatch_now(CreateFile::image($image, 'Image', new Language(['code' => 'en']), $user));
        $file1 = dispatch_now(CreateFile::image($image, 'Image', new Language(['code' => 'en']), $user));

        dispatch_now(new SyncFiles($entity, [$file->id, $file1->id => ['weight' => 3], 30, 40]));

        $files = $entity->getSyncedFiles();

        $this->assertEquals(2, $files->count());
        $this->assertEquals($file->id, $files->first()->id);
        $this->assertEquals($file1->id, $files->last()->id);
    }
}

