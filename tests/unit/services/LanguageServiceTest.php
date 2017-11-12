<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Models\Language;
use Gzero\Core\Services\LanguageService;

class LanguageServiceTest extends Unit {

    /** @var \Core\UnitTester */
    protected $tester;

    /** @var LanguageService */
    protected $service;

    protected function _before()
    {
        $this->service = resolve(LanguageService::class);
    }

    /** @test */
    public function canGetAllAvailableLanguages()
    {
        $languages = $this->service->getAll();
        $this->tester->assertGreaterThan(2, $languages->count());
    }

    /** @test */
    public function shouldUseCacheToStoreLanguages()
    {
        $languagesCount = $this->service->getAll()->count();
        factory(Language::class)->create(['is_enabled' => true]);

        // Should still have old language collection
        $this->tester->assertEquals($languagesCount, $this->service->getAll()->count());
        $this->service->refresh();
        // Now should have new language too
        $this->tester->assertEquals($languagesCount + 1, $this->service->getAll()->count());
    }


    /** @test */
    public function canGetAllEnabledLanguages()
    {
        $enabledLanguagesCount = $this->service->getAllEnabled()->count();

        factory(Language::class, 3)->create(['is_enabled' => false]);
        $this->service->refresh();

        $this->tester->assertEquals($enabledLanguagesCount, $this->service->getAllEnabled()->count());

        factory(Language::class, 2)->create(['is_enabled' => true]);
        $this->service->refresh();

        $this->tester->assertEquals($enabledLanguagesCount + 2, $this->service->getAllEnabled()->count());
    }

    /** @test */
    public function canGetAppCurrentLanguage()
    {
        $this->tester->getApplication()->setLocale('en');
        $this->tester->assertEquals('en', $this->service->getCurrent()->code);

        $this->tester->getApplication()->setLocale('pl');
        $this->tester->assertEquals('pl', $this->service->getCurrent()->code);
    }

}

