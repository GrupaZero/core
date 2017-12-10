<?php namespace Core;

use Gzero\Core\Models\Language;
use Gzero\Core\Models\Option;
use Gzero\Core\Models\OptionCategory;
use Gzero\Core\Services\OptionService;
use Gzero\InvalidArgumentException;

class OptionServiceTest extends \Codeception\Test\Unit {

    /** @var UnitTester */
    protected $tester;

    /** @var OptionService */
    protected $service;

    protected $expectedOptions;

    protected function _before()
    {
        $this->recreateRepository();
        $this->setExpectedOptions();
    }

    /** @test */
    public function itChecksExistenceOfCategoryWhenGettingAnOption()
    {
        $categoryKey = 'nonexistent category';

        $this->assertNull(OptionCategory::getByKey($categoryKey));

        try {
            $this->service->getOptions($categoryKey);
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Category nonexistent category does not exist', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function ItChecksExistenceOfCategoryAndOptionWhenGettingAnNonExistingOption()
    {
        $optionKey   = 'nonexistent option';
        $categoryKey = 'nonexistent category';

        $this->assertNull(Option::getByKey($optionKey));
        $this->assertNull(OptionCategory::getByKey($categoryKey));

        try {
            $this->service->getOption($categoryKey, $optionKey);
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Category nonexistent category does not exist', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function ItChecksExistenceOfOptionWhenGettingAnOptionFromExistingCategory()
    {
        $optionKey   = 'nonexistent option';
        $categoryKey = 'general';

        $this->assertNull(Option::getByKey($optionKey));
        $this->assertNotNull(OptionCategory::getByKey($categoryKey));

        try {
            $this->service->getOption($categoryKey, $optionKey);
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Option nonexistent option in category general does not exist', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function ItChecksExistenceOfCategoryWhenDeletingAnOption()
    {
        $categoryKey = 'nonexistent category';

        $this->assertNull(OptionCategory::getByKey($categoryKey));

        try {
            $this->service->deleteCategory($categoryKey);
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Category nonexistent category does not exist', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function ItChecksExistenceOfCategoryAndOptionWhenDeletingAnNonExistingOption()
    {
        $optionKey   = 'nonexistent option';
        $categoryKey = 'nonexistent category';

        $this->assertNull(Option::getByKey($optionKey));
        $this->assertNull(OptionCategory::getByKey($categoryKey));

        try {
            $this->service->deleteOption($categoryKey, $optionKey);
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Category nonexistent category does not exist', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function ItChecksExistenceOfOptionWhenDeletingAnOption()
    {
        $optionKey   = 'nonexistent option';
        $categoryKey = 'general';

        $this->assertNull(Option::getByKey($optionKey));
        $this->assertNotNull(OptionCategory::getByKey($categoryKey));

        try {
            $this->service->deleteOption($categoryKey, $optionKey);
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Option nonexistent option in category general does not exist', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function ItGetsOptionFromGeneralCategory()
    {
        $optionKey   = 'site_name';
        $categoryKey = 'general';

        try {
            $this->assertEquals(
                $this->expectedOptions[$categoryKey][$optionKey]['en'],
                $this->service->getOption($categoryKey, $optionKey)['en']
            );
        } catch (InvalidArgumentException $e) {
            $this->fail('Exception should not be thrown');
            return;
        }

        $this->assertNotNull(Option::getByKey($optionKey));
        $this->assertNotNull(OptionCategory::getByKey($categoryKey));
    }

    /** @test */
    public function ItGetsAllOptionsFromGeneralCategory()
    {
        $categoryKey = 'general';

        try {
            $this->assertEquals(
                collect($this->expectedOptions[$categoryKey]),
                $this->service->getOptions($categoryKey)
            );
        } catch (InvalidArgumentException $e) {
            $this->fail('Exception should not be thrown');
            return;
        }

        $this->assertNotNull(OptionCategory::getByKey($categoryKey));
    }

    /** @test */
    public function CanCreateCategory()
    {
        $categoryKey = 'New category';

        $this->service->createCategory($categoryKey);

        $this->assertNotNull(OptionCategory::getByKey($categoryKey));
    }

    /** @test */
    public function CanCreateOption()
    {
        $categoryKey = 'general';
        $optionKey   = 'some option';
        $value       = ['en' => 'new option value'];

        try {
            $this->service->updateOrCreateOption($categoryKey, $optionKey, $value);
        } catch (InvalidArgumentException $e) {
            $this->fail('Exception should not be thrown');
            return;
        }

        $savedOption = OptionCategory::getByKey($categoryKey)->options()->where(['key' => $optionKey])->first();
        $this->assertNotNull($savedOption);
        $this->assertEquals($value, $savedOption->value);

        $this->recreateRepository();

        try {
            $this->assertEquals($value, $this->service->getOption($categoryKey, $optionKey));
        } catch (InvalidArgumentException $e) {
            $this->fail('Exception should not be thrown');
            return;
        }

        $this->assertNotNull(Option::getByKey($optionKey));
        $this->assertNotNull(OptionCategory::getByKey($categoryKey));
    }


    /** @test */
    public function CanDeleteCategory()
    {
        $categoryKey = 'general';

        try {
            $this->service->deleteCategory($categoryKey);
        } catch (InvalidArgumentException $e) {
            $this->fail('Exception should not be thrown');
            return;
        }

        $this->assertNull(OptionCategory::getByKey($categoryKey));
    }

    /** @test */
    public function canDeleteOption()
    {
        $categoryKey = 'general';
        $optionKey   = 'site_name';

        try {
            $this->service->deleteOption('general', $optionKey);
        } catch (InvalidArgumentException $e) {
            $this->fail('Exception should not be thrown');
            return;
        }

        $this->assertFalse(
            OptionCategory::getByKey($categoryKey)->options()->
            where(['key' => $optionKey])->exists()
        );

        $this->assertNull(Option::getByKey($optionKey));
    }

    private function recreateRepository()
    {
        $this->service = new OptionService(
            new OptionCategory(),
            new Option(),
            new \Illuminate\Cache\CacheManager($this->tester->getApplication())
        );
    }

    private function setExpectedOptions()
    {
        $this->expectedOptions = [
            'general' => [
                'site_name'          => [],
                'site_desc'          => [],
                'default_page_size'  => [],
                'cookies_policy_url' => [],
            ],
            'seo'     => [
                'seoDescLength'     => [],
                'googleAnalyticsId' => [],
            ]
        ];

        // Propagate Lang options based on gzero config
        foreach ($this->expectedOptions as $categoryKey => $category) {
            foreach ($this->expectedOptions[$categoryKey] as $key => $option) {
                foreach (Language::all()->toArray() as $lang) {
                    if ($categoryKey != 'general') {
                        $this->expectedOptions[$categoryKey][$key][$lang['code']] = config('gzero.' . $categoryKey . '.' . $key);
                    } else {
                        $value = $this->getDefaultValueForGeneral($key);

                        $this->expectedOptions[$categoryKey][$key][$lang['code']] = $value;
                    }
                }
            }
        }
    }

    /**
     * It generates default value for general options
     *
     * @param $key
     *
     * @return mixed|string
     */
    private static function getDefaultValueForGeneral($key)
    {
        switch ($key) {
            case 'site_name':
                $value = "GZERO-CMS"; // Hardcoded from default migration
                break;
            case 'site_desc':
                $value = "GZERO-CMS Content management system.";
                break;
            default:
                $value = config('gzero.' . $key);
                return $value;
        }
        return $value;
    }
}
