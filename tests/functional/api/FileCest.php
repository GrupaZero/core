<?php namespace Core\api;

use Carbon\Carbon;
use Core\FunctionalTester;
use Gzero\Core\Jobs\AddFileTranslation;
use Gzero\Core\Jobs\CreateFile;
use Gzero\Core\Jobs\UpdateFile;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileCest {

    public function _before(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        Storage::fake('uploads');
    }

    public function shouldGetListOfFiles(FunctionalTester $I)
    {
        $user  = factory(User::class)->create();
        $en    = new Language(['code' => 'en']);
        $pl    = new Language(['code' => 'pl']);
        $image = UploadedFile::fake()->image('file.jpg')->size(10);

        $file = dispatch_now(CreateFile::image($image, 'New Image', $en, $user, [
            'info'        => 'info',
            'description' => 'My image',
            'is_active'   => true
        ]));

        dispatch_now(new AddFileTranslation($file, 'Nowy Plik', $pl, $user,
            ['description' => 'Opis']
        ));

        $I->sendGet(apiUrl('files'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                [
                    'type'         => 'image',
                    'name'         => 'file',
                    'extension'    => 'jpg',
                    'size'         => 10240,
                    'mime_type'    => 'image/jpeg',
                    'info'         => 'info',
                    'is_active'    => true,
                    'translations' => [
                        [
                            'language_code' => 'en',
                            'title'         => 'New Image',
                            'description'   => 'My image'
                        ],
                        [
                            'language_code' => 'pl',
                            'title'         => 'Nowy Plik',
                            'description'   => 'Opis'
                        ]
                    ]
                ]
            ]
        );

        $thumbPath = head($I->grabDataFromResponseByJsonPath('data[0]thumb'));
        $I->assertRegExp($I->generateCroppaRegExp($file->name, $file->extension), $thumbPath);
    }

    public function shouldBeAbleToFilterListOfFilesByCreatedAt(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('file.jpg')->size(10);

        dispatch_now(CreateFile::image($image, 'New Image', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('files?created_at=2017-10-09,2017-10-10'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        $I->sendGET(apiUrl('files?created_at=!2017-10-09,2017-10-10'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'image',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'New Image'
                    ]
                ]
            ]
        );
    }

    public function shouldBeAbleToFilterListOfFilesByUpdatedAt(FunctionalTester $I)
    {
        $fourDaysAgo = Carbon::now()->subDays(4);
        $yesterday   = Carbon::yesterday()->format('Y-m-d');
        $tomorrow    = Carbon::tomorrow()->format('Y-m-d');

        $file = $I->haveFile([
            'type'         => 'image',
            'created_at'   => $fourDaysAgo,
            'updated_at'   => $fourDaysAgo,
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => "Four day's ago file's title"
                ]
            ]
        ]);

        $I->sendGET(apiUrl("files?updated_at=$yesterday,$tomorrow"));
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        dispatch_now((new UpdateFile($file, ['info' => 'new info'])));

        $I->sendGET(apiUrl("files?updated_at=$yesterday,$tomorrow"));
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                [
                    'type'         => 'image',
                    'info'         => 'new info',
                    'translations' => [
                        [
                            'language_code' => 'en',
                            'title'         => "Four day's ago file's title"
                        ]
                    ]
                ]
            ]
        );
    }

    public function shouldBeAbleToFilterListOfFilesByType(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('file.jpg')->size(10);
        $document = UploadedFile::fake()->image('file.txt')->size(10);

        dispatch_now(CreateFile::image($image, 'Image Title', $language, $user, ['is_active' => true]));
        dispatch_now(CreateFile::document($document, 'Document Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('files?type=image'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'image',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Image Title'
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'document',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Document Title'
                    ]
                ]
            ]
        );

        $I->sendGET(apiUrl('files?type=!image'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'document',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Document Title'
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'image',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Image Title'
                    ]
                ]
            ]
        );
    }

    public function shouldBeAbleToSortListOfFilesByType(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('file.jpg')->size(10);
        $document = UploadedFile::fake()->image('file.txt')->size(10);

        dispatch_now(CreateFile::image($image, 'Image Title', $language, $user, ['is_active' => true]));
        dispatch_now(CreateFile::document($document, 'Document Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('files?sort=type'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].type');
        $second = $I->grabDataFromResponseByJsonPath('data[1].type');

        $I->assertEquals('document', head($first));
        $I->assertEquals('image', head($second));

        $I->sendGET(apiUrl('files?sort=-type'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].type');
        $second = $I->grabDataFromResponseByJsonPath('data[1].type');

        $I->assertEquals('image', head($first));
        $I->assertEquals('document', head($second));
    }

    public function shouldBeAbleToSortListOfFilesByTranslationsTitle(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('file.jpg')->size(10);
        $document = UploadedFile::fake()->image('file.txt')->size(10);

        dispatch_now(CreateFile::image($image, 'Image Title', $language, $user, ['is_active' => true]));
        dispatch_now(CreateFile::document($document, 'Document Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('files?translations[language_code]=en&sort=translations.title'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].translations[0].title');
        $second = $I->grabDataFromResponseByJsonPath('data[1].translations[0].title');

        $I->assertEquals('Document Title', head($first));
        $I->assertEquals('Image Title', head($second));

        $I->sendGET(apiUrl('files?translations[language_code]=en&sort=-translations.title'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].translations[0].title');
        $second = $I->grabDataFromResponseByJsonPath('data[1].translations[0].title');

        $I->assertEquals('Image Title', head($first));
        $I->assertEquals('Document Title', head($second));
    }

    public function shouldBeAbleToFilterListOfFilesByAuthorId(FunctionalTester $I)
    {
        $user1    = factory(User::class)->create();
        $user2    = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('file.jpg')->size(10);
        $document = UploadedFile::fake()->image('file.txt')->size(10);

        dispatch_now(CreateFile::image($image, 'Image Title', $language, $user1, ['is_active' => true]));
        dispatch_now(CreateFile::document($document, 'Document Title', $language, $user2, ['is_active' => true]));

        $I->sendGET(apiUrl('files?author_id=' . $user1->id));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'image',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Image Title',
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'basic',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Document Title',
                    ]
                ]
            ]
        );

        $I->sendGET(apiUrl('files?author_id=' . $user2->id));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'document',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Document Title',
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'image',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Image Title',
                    ]
                ]
            ]
        );
    }

    public function shouldBeAbleToSortListOfFilesByAuthorId(FunctionalTester $I)
    {
        $user1    = factory(User::class)->create();
        $user2    = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('file.jpg')->size(10);
        $document = UploadedFile::fake()->image('file.txt')->size(10);

        dispatch_now(CreateFile::image($image, 'Image Title', $language, $user1, ['is_active' => true]));
        dispatch_now(CreateFile::document($document, 'Document Title', $language, $user2, ['is_active' => true]));

        $I->sendGET(apiUrl('files?sort=author_id'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].author_id');
        $second = $I->grabDataFromResponseByJsonPath('data[1].author_id');

        $I->assertEquals($user1->id, head($first));
        $I->assertEquals($user2->id, head($second));

        $I->sendGET(apiUrl('files?sort=-author_id'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].author_id');
        $second = $I->grabDataFromResponseByJsonPath('data[1].author_id');

        $I->assertEquals($user2->id, head($first));
        $I->assertEquals($user1->id, head($second));
    }

    public function shouldBeAbleToFilterListOfFilesByIsActive(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('file.jpg')->size(10);

        dispatch_now(CreateFile::image($image, 'Active', $language, $user, ['is_active' => true]));
        dispatch_now(CreateFile::image($image, 'Not Active', $language, $user, ['is_active' => false]));

        $I->sendGET(apiUrl('files?is_active=true'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Active']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Not Active']
        );

        $I->sendGET(apiUrl('files?is_active=false'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Not Active']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Active']
        );
    }

    public function shouldBeAbleToFilterListOfFilesByName(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('image.jpg')->size(10);
        $document = UploadedFile::fake()->image('document.txt')->size(10);

        dispatch_now(CreateFile::image($image, 'Image Title', $language, $user, ['is_active' => true]));
        dispatch_now(CreateFile::document($document, 'Document Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('files?name=image'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type' => 'image',
                'name' => 'image'
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type' => 'document',
                'name' => 'document'
            ]
        );

        $I->sendGET(apiUrl('files?name=!image'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type' => 'document',
                'name' => 'document'
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type' => 'image',
                'name' => 'image'
            ]
        );
    }

    public function shouldBeAbleToSortListOfFilesByName(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('image.jpg')->size(10);
        $document = UploadedFile::fake()->image('document.txt')->size(10);

        dispatch_now(CreateFile::image($image, 'Image Title', $language, $user, ['is_active' => true]));
        dispatch_now(CreateFile::document($document, 'Document Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('files?sort=name'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].name');
        $second = $I->grabDataFromResponseByJsonPath('data[1].name');

        $I->assertEquals('document', head($first));
        $I->assertEquals('image', head($second));

        $I->sendGET(apiUrl('files?sort=-name'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].name');
        $second = $I->grabDataFromResponseByJsonPath('data[1].name');

        $I->assertEquals('image', head($first));
        $I->assertEquals('document', head($second));
    }

    public function shouldBeAbleToFilterListOfFilesByExtension(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('image.jpg')->size(10);
        $document = UploadedFile::fake()->image('document.txt')->size(10);

        dispatch_now(CreateFile::image($image, 'Image Title', $language, $user, ['is_active' => true]));
        dispatch_now(CreateFile::document($document, 'Document Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('files?extension=jpg'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'      => 'image',
                'extension' => 'jpg'
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'      => 'document',
                'extension' => 'txt'
            ]
        );

        $I->sendGET(apiUrl('files?extension=!jpg'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'      => 'document',
                'extension' => 'txt'
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'      => 'image',
                'extension' => 'jpg'
            ]
        );
    }

    public function shouldBeAbleToSortListOfFilesByExtension(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('image.jpg')->size(10);
        $document = UploadedFile::fake()->image('document.txt')->size(10);

        dispatch_now(CreateFile::image($image, 'Image Title', $language, $user, ['is_active' => true]));
        dispatch_now(CreateFile::document($document, 'Document Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('files?sort=extension'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].extension');
        $second = $I->grabDataFromResponseByJsonPath('data[1].extension');

        $I->assertEquals('jpg', head($first));
        $I->assertEquals('txt', head($second));

        $I->sendGET(apiUrl('files?sort=-extension'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].extension');
        $second = $I->grabDataFromResponseByJsonPath('data[1].extension');

        $I->assertEquals('txt', head($first));
        $I->assertEquals('jpg', head($second));
    }

    public function shouldBeAbleToFilterListOfFilesByMimeType(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('image.jpg')->size(10);
        $document = UploadedFile::fake()->image('document.txt')->size(10);

        dispatch_now(CreateFile::image($image, 'Image Title', $language, $user, ['is_active' => true]));
        dispatch_now(CreateFile::document($document, 'Document Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('files?mime_type=image/jpeg'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'      => 'image',
                'mime_type' => 'image/jpeg'
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'      => 'document',
                'mime_type' => 'text/plain'
            ]
        );

        $I->sendGET(apiUrl('files?mime_type=!image/jpeg'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'      => 'document',
                'mime_type' => 'text/plain'
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'      => 'image',
                'mime_type' => 'image/jpeg'
            ]
        );
    }

    public function shouldBeAbleToSortListOfFilesByMimeType(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('image.jpg')->size(10);
        $document = UploadedFile::fake()->image('document.txt')->size(10);

        dispatch_now(CreateFile::image($image, 'Image Title', $language, $user, ['is_active' => true]));
        dispatch_now(CreateFile::document($document, 'Document Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('files?sort=mime_type'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].mime_type');
        $second = $I->grabDataFromResponseByJsonPath('data[1].mime_type');

        $I->assertEquals('image/jpeg', head($first));
        $I->assertEquals('text/plain', head($second));

        $I->sendGET(apiUrl('files?sort=-mime_type'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].mime_type');
        $second = $I->grabDataFromResponseByJsonPath('data[1].mime_type');

        $I->assertEquals('text/plain', head($first));
        $I->assertEquals('image/jpeg', head($second));
    }

    public function shouldBeAbleToFilterListOfFilesBySize(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('image.jpg')->size(10);
        $document = UploadedFile::fake()->image('document.txt')->size(20);

        dispatch_now(CreateFile::image($image, 'Image Title', $language, $user, ['is_active' => true]));
        dispatch_now(CreateFile::document($document, 'Document Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('files?size=<=10240'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type' => 'image',
                'size' => 10240
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type' => 'document',
                'size' => 20480
            ]
        );

        $I->sendGET(apiUrl('files?size=>10240'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type' => 'document',
                'size' => 20480
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type' => 'image',
                'size' => 10240
            ]
        );
    }

    public function shouldNotBeAbleToFilterListOfFilesBySizeUsingStringValue(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $document = UploadedFile::fake()->image('document.txt')->size(20);

        dispatch_now(CreateFile::document($document, 'Document Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('files?size=<=fourty'));

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'size' => [
                        'The size format is invalid.'
                    ]
                ]
            ]
        );
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->dontSeeResponseContainsJson(
            [
                'type' => 'document',
                'size' => 20480
            ]
        );
    }

    public function shouldGetSingleFile(FunctionalTester $I)
    {
        $user  = factory(User::class)->create();
        $en    = new Language(['code' => 'en']);
        $pl    = new Language(['code' => 'pl']);
        $image = UploadedFile::fake()->image('file.jpg')->size(10);

        $file = dispatch_now(CreateFile::image($image, 'New Image', $en, $user, [
            'info'        => 'info',
            'description' => 'My image',
            'is_active'   => true
        ]));

        dispatch_now(new AddFileTranslation($file, 'Nowy Plik', $pl, $user,
            ['description' => 'Opis']
        ));

        $I->sendGET(apiUrl('files', ['id' => $file->id]));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'image',
                'name'         => 'file',
                'extension'    => 'jpg',
                'size'         => 10240,
                'mime_type'    => 'image/jpeg',
                'info'         => 'info',
                'is_active'    => true,
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'New Image',
                        'description'   => 'My image'
                    ],
                    [
                        'language_code' => 'pl',
                        'title'         => 'Nowy Plik',
                        'description'   => 'Opis'
                    ]
                ]
            ]
        );

        $thumbPath = head($I->grabDataFromResponseByJsonPath('thumb'));
        $I->assertRegExp($I->generateCroppaRegExp('file', 'jpg'), $thumbPath);
    }

    public function shouldNotBeAbleToGetNonExistingFile(FunctionalTester $I)
    {
        $I->sendGET(apiUrl('files', ['id' => 100]));
        $I->seeResponseCodeIs(404);
    }

    public function canCreateFile(FunctionalTester $I)
    {
        $image = UploadedFile::fake()->image('file.png', 100, 100);

        $I->sendPOST(apiUrl('files'),
            [
                'type'          => 'image',
                'info'          => ['option' => 'value'],
                'is_active'     => true,
                'language_code' => 'en',
                'title'         => 'New Image',
                'description'   => 'My image'
            ], ['file' => $image]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'image',
                'name'         => 'file',
                'extension'    => 'png',
                'size'         => $image->getSize(),
                'mime_type'    => $image->getMimeType(),
                'info'         => ['option' => 'value'],
                'is_active'    => true,
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'New Image',
                        'description'   => 'My image'
                    ]
                ]
            ]
        );

        Storage::disk('uploads')->assertExists('images/file.png');
        $thumbPath = head($I->grabDataFromResponseByJsonPath('thumb'));
        $I->assertRegExp($I->generateCroppaRegExp('file', 'png'), $thumbPath);
    }

    public function canUpdateFile(FunctionalTester $I)
    {
        $file = $I->haveFile([
            'type'      => 'image',
            'info'      => ['option' => 'value'],
            'is_active' => true
        ]);

        $I->sendPATCH(apiUrl('files', ['id' => $file->id]),
            [
                'info'      => ['option' => 'changed value'],
                'is_active' => false,
            ]
        );

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'info'      => ['option' => 'changed value'],
                'is_active' => false
            ]
        );
    }

    public function canDeleteFile(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('file.jpg')->size(10);

        $file = dispatch_now(CreateFile::image($image, 'New Image', $language, $user, ['is_active' => true]));

        Storage::disk('uploads')->assertExists('images/file.jpg');

        $I->sendDELETE(apiUrl('files', ['id' => $file->id]));

        $I->seeResponseCodeIs(204);
        Storage::disk('uploads')->assertMissing('images/file.jpg');
    }

    public function shouldBeAbleToGetFileTranslations(FunctionalTester $I)
    {
        $user  = factory(User::class)->create();
        $en    = new Language(['code' => 'en']);
        $pl    = new Language(['code' => 'pl']);
        $image = UploadedFile::fake()->image('file.jpg')->size(10);

        $file = dispatch_now(CreateFile::image($image, 'Original title', $en, $user, [
            'description' => 'Original description'
        ]));

        dispatch_now(new AddFileTranslation($file, 'Modified title', $en, $user, [
            'description' => 'Modified description'
        ]));

        dispatch_now(new AddFileTranslation($file, 'Oryginalny tytuł', $pl, $user, [
            'description' => 'Originalny opis'
        ]));

        dispatch_now(new AddFileTranslation($file, 'Nowy tytuł', $pl, $user, [
            'description' => 'Nowy opis'
        ]));

        $I->sendGET(apiUrl("files/$file->id/translations"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                [
                    'language_code' => 'en',
                    'title'         => 'Modified title',
                    'description'   => 'Modified description'
                ],
                [
                    'language_code' => 'pl',
                    'title'         => 'Nowy tytuł',
                    'description'   => 'Nowy opis'
                ]
            ]
        );

        $I->dontSeeResponseContainsJson(
            [
                [
                    'language_code' => 'en',
                    'title'         => 'Original title',
                    'description'   => 'Original description'
                ],
                [
                    'language_code' => 'pl',
                    'title'         => 'Oryginalny tytuł',
                    'description'   => 'Originalny opis'
                ]
            ]
        );
    }

    public function shouldBeAbleToFilterListOfFileTranslationsByLanguageCode(FunctionalTester $I)
    {
        $user  = factory(User::class)->create();
        $en    = new Language(['code' => 'en']);
        $pl    = new Language(['code' => 'pl']);
        $image = UploadedFile::fake()->image('file.jpg')->size(10);

        $file = dispatch_now(CreateFile::image($image, 'Example Title', $en, $user, ['is_active' => true]));

        dispatch_now(new AddFileTranslation($file, 'Przykładowy Tytuł', $pl, $user));

        $I->sendGET(apiUrl("files/$file->id/translations?language_code=en"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                ['language_code' => 'en', 'title' => 'Example Title']
            ]
        );

        $I->dontSeeResponseContainsJson(
            [
                ['language_code' => 'pl', 'title' => 'Przykładowy Tytuł']
            ]
        );

        $I->sendGET(apiUrl("files/$file->id/translations?language_code=pl"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                ['language_code' => 'pl', 'title' => 'Przykładowy Tytuł']
            ]
        );

        $I->dontSeeResponseContainsJson(
            [
                ['language_code' => 'en', 'title' => 'Example Title']
            ]
        );
    }

    public function shouldBeAbleToFilterListOfFileTranslationsByAuthorId(FunctionalTester $I)
    {
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $en    = new Language(['code' => 'en']);
        $pl    = new Language(['code' => 'pl']);
        $image = UploadedFile::fake()->image('file.jpg')->size(10);

        $file = dispatch_now(CreateFile::image($image, 'Example Title', $en, $user1, ['is_active' => true]));

        dispatch_now(new AddFileTranslation($file, 'Translation from first user', $en, $user1));
        dispatch_now(new AddFileTranslation($file, 'Translation from second user', $pl, $user2));

        $I->sendGET(apiUrl("files/$file->id/translations?author_id=$user1->id"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                ['title' => 'Translation from first user']
            ]
        );

        $I->dontSeeResponseContainsJson(
            [
                ['title' => 'Translation from second user']
            ]
        );

        $I->sendGET(apiUrl("files/$file->id/translations?author_id=$user2->id"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                ['title' => 'Translation from second user']
            ]
        );

        $I->dontSeeResponseContainsJson(
            [
                ['title' => 'Translation from first user']
            ]
        );
    }

    public function shouldBeAbleToFilterListOfFileTranslationsByCreatedAt(FunctionalTester $I)
    {
        $fourDaysAgo = Carbon::now()->subDays(4);
        $yesterday   = Carbon::yesterday()->format('Y-m-d');
        $today       = Carbon::now()->format('Y-m-d');

        $file = $I->haveFile([
            'type'         => 'image',
            'created_at'   => $fourDaysAgo,
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => "Four day's ago files's content",
                    'created_at'    => $fourDaysAgo
                ]
            ]
        ]);

        $I->sendGET(apiUrl("files/$file->id/translations?created_at=$yesterday,$today"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        $I->sendGET(apiUrl("files/$file->id/translations?created_at=!$yesterday,$today"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                ['title' => "Four day's ago files's content"]
            ]
        );
    }

    public function shouldBeAbleToFilterListOfFileTranslationsByUpdatedAt(FunctionalTester $I)
    {
        $fourDaysAgo = Carbon::now()->subDays(4);
        $yesterday   = Carbon::yesterday()->format('Y-m-d');
        $today       = Carbon::now()->format('Y-m-d');

        $file = $I->haveFile([
            'type'         => 'image',
            'created_at'   => $fourDaysAgo,
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => "Four day's ago file's content",
                    'updated_at'    => $fourDaysAgo
                ]
            ]
        ]);

        $I->sendGET(apiUrl("files/$file->id/translations?updated_at=$yesterday,$today"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        $I->sendGET(apiUrl("files/$file->id/translations?updated_at=!$yesterday,$today"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                ['title' => "Four day's ago file's content"]
            ]
        );
    }

    public function canCreateFileTranslation(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);
        $image    = UploadedFile::fake()->image('file.jpg')->size(10);

        $file = dispatch_now(CreateFile::image($image, 'Original Title', $language, $user));

        $I->sendPOST(apiUrl("files/$file->id/translations"),
            [
                'language_code' => 'en',
                'title'         => 'Example title',
                'description'   => 'Example description'
            ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'language_code' => 'en',
                'title'         => 'Example title',
                'description'   => 'Example description'
            ]
        );

        $I->sendPOST(apiUrl("files/$file->id/translations"),
            [
                'language_code' => 'pl',
                'title'         => 'Nowy tytuł',
                'description'   => 'Nowy opis'
            ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'language_code' => 'pl',
                'title'         => 'Nowy tytuł',
                'description'   => 'Nowy opis'
            ]
        );
    }

    public function canDeleteFileTranslation(FunctionalTester $I)
    {
        $user  = factory(User::class)->create();
        $en    = new Language(['code' => 'en']);
        $pl    = new Language(['code' => 'pl']);
        $image = UploadedFile::fake()->image('file.jpg')->size(10);

        $file        = dispatch_now(CreateFile::image($image, 'Original Title', $en, $user));
        $translation = dispatch_now(new AddFileTranslation($file, 'Przykładowy Tytuł', $pl, $user));

        $I->sendDELETE(apiUrl("files/$file->id/translations", ['translationId' => $translation->id]));

        $I->seeResponseCodeIs(204);
    }


    public function cantDeleteNotExistingFileTranslation(FunctionalTester $I)
    {
        $file = $I->haveFile([
            'type'         => 'image',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => "File title"
                ]
            ]
        ]);

        $I->sendDELETE(apiUrl("files/$file->id/translations", ['translationId' => 100]));

        $I->seeResponseCodeIs(404);
    }
}
