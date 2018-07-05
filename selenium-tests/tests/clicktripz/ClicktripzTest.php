<?php

namespace My\tests\clicktripz; // Note the "My" namespace maps to the "tests" folder, as defined in the autoload part of `composer.json`.

use Facebook\WebDriver\WebDriverBy;
use Lmc\Steward\Test\AbstractTestCase;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * @group ClickTripz
 */
 
class ClicktripzTest extends AbstractTestCase
{
    protected $conf;

    public function setUp()
    {
        $this->wd->get('https://www.clicktripz.com/test.php');

    }

    public function tearDown()
    {
        $this->wd->quit();
    }

    public function testHotelCitywide()
    {
        $screen_capture_loc = 'logs/screen_captures/hotel_citywide/';
        $city_name = 'Chico';
        $check_in_date = '07/06/2018';
        $check_out_date = '07/08/2018';

        // waits for city input box to be displayed
        $this->waitForId('city',true);

        // clears and inputs City
        $this->findById('city')->clear();
        $this->findById('city')->sendKeys($city_name);

        // clears and inputs Check-In date
        $this->findById('date1')->clear();
        $this->findById('date1')->sendKeys($check_in_date);

        // clears and inputs Check-Out date
        $this->findById('date2')->clear();
        $this->waitForXpath('//*[@class=" ui-datepicker-week-end "]/*[text()= "8"]',true);
        $this->findByXpath('//*[@class=" ui-datepicker-week-end "]/*[text()= "8"]')->click();

        // wait for date picker to close
        $this->wd->wait(10, 1000)->
        until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::xpath("//*[contains(@style, 'display: block')]")));

        // sets the guest dropdown to 2
        $this->waitForId('guests',true);
        $this->findById('guests')->click();
        $this->wd->findElement(WebDriverBy::cssSelector('select[id="guests"] option[value="2"]'))->click();

        // click the search button
        $this->waitForId('ctAnchor',true);
        $this->findByCss('button#search-button.btn.btn-primary')->click();

        // minimize window
        $this->wd->manage()->window()->setSize(new WebDriverDimension(0, -1000));

        // Switch to newly opened window
        $window_handles = $this->wd->getWindowHandles();
        $parent_window =$this->wd->getWindowHandle();
        $last_window = end($window_handles);
        $this->wd->switchTo()->window($last_window);

        $this->wd->wait(60, 1000)->
        until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector("a.button.close-modal")));

        // Verify the modal is displayed
        $modal_displayed = $this->findByCss('a.button.close-modal')->isDisplayed();
        $this->assertTrue($modal_displayed);

        // Take screenshot pre and post modal
        $this->wd->takeScreenshot($screen_capture_loc . 'exit_unit_pre_modal.png');
        $this->findByCss('a.button.close-modal')->click();
        $this->wd->takeScreenshot($screen_capture_loc . 'exit_unit_tab_1.png');

        // Verify the correct search data was displayed
        $search_criteria = $this->findByXpath("//div[@class= 'search-data']/*/span")->getText();
        $this->assertContains( $city_name, $search_criteria);
        $this->assertContains( $check_in_date, $search_criteria);
        $this->assertContains( $check_out_date, $search_criteria);

        // Capture and verify the URL is for Hotel Citywide
        $current_url = $this->wd->getCurrentURL();
        $test_url = 'https://www.clicktripz.com/rates/search/index.php?city='. $city_name .'&checkInDate=' . $check_in_date .
            '&checkOutDate=' . $check_out_date . '&rooms=1&guests=2';
        $this->assertContains( $test_url, $current_url);

        $hotel_citywide_file = fopen('logs/url_captures/hotel_citywide.txt', 'w');

        fwrite($hotel_citywide_file, "Hotel Citywide Exit Unit Url: $current_url
");
        fclose($hotel_citywide_file);

        // Get tab count
        $tab_count = $this->findByXpath('//*[@id="ct-header"]/ul')->getAttribute('data-tab-count');
        $this->assertEquals('7', $tab_count);

        // call to function to loop thru the tabs
        $this->tab_captures($tab_count, $screen_capture_loc);

        // verify all tabs have been clicked
        $this->wd->switchTo($parent_window);
        $untouched_tabs = count($this->wd->findElements(WebDriverBy::xpath("//li[contains(@class, 'visited')]")));
        $this->assertEquals($tab_count, $untouched_tabs);
    }

    public function testFlights()
    {
        $screen_capture_loc = 'logs/screen_captures/flights/';
        $city_from = 'Sacramento';
        $city_to = 'Denver';
        $departing_date = '07/06/2018';
        $returning_date = '07/08/2018';

        // waits for flight tab to be displayed
        $this->waitForCss("a[href='/test_flights.php']",true);
        $this->findByCss("a[href='/test_flights.php']")->click();

        // waits for city input box to be displayed and inputs from & to
        $this-> waitForId("city1");
        $this->findById('city1')->clear()->sendKeys($city_from);
        $this->findById('city2')->clear()->sendKeys($city_to);

        // sets the departing and returning dates
        $this->findById('date1')->clear()->sendKeys($departing_date);
        $this->findById('date2')->clear()->sendKeys($returning_date);

        // waits and sets the travelers count
        $this->waitForId('travelers',true);
        $this->findById('travelers')->click();
        $this->wd->findElement(WebDriverBy::cssSelector('select[id="travelers"] option[value="2"]'))->click();

        // waits for compare flight options to load before clicking search button
        $this->waitForId('ct-anchor',true);
        $this->findById('search-button')->click();

        // minmize window, set to negative number, webdriver has no options to minimize
        $this->wd->manage()->window()->setSize(new WebDriverDimension(0, -1000));

        // Switch to newly opened window
        $window_handles = $this->wd->getWindowHandles();
        $parent_window =$this->wd->getWindowHandle();
        $last_window = end($window_handles);
        $this->wd->switchTo()->window($last_window);

        // wait for modal to be present
        $this->wd->wait(60, 1000)->
        until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector("p.close-button")));

        // Verify the modal is displayed
        $modal_displayed = $this->findByCss('a.button.close-modal')->isDisplayed();
        $this->assertTrue($modal_displayed);

        // Take screenshot pre and post modal
        $this->wd->takeScreenshot($screen_capture_loc . 'exit_unit_pre_modal.png');
        $this->findByCss('a.button.close-modal')->click();
        $this->wd->takeScreenshot($screen_capture_loc . 'exit_unit_tab_1.png');

        // Verify the correct search data was displayed
        $search_criteria = $this->findByXpath("//div[@class= 'search-data']/*/span")->getText();
        $this->assertContains( $city_to, $search_criteria);
        $this->assertContains( $departing_date, $search_criteria);
        $this->assertContains( $returning_date, $search_criteria);

        // Capture and verify the URL is for Hotel Citywide
        $current_url = $this->wd->getCurrentURL();
        $test_url = 'https://www.clicktripz.com/rates/search/index.php?departureDate=' . $departing_date . '&returnDate=' .
            $returning_date . '&from=' . $city_from . '&to=' . $city_to ;
        $this->assertContains( $test_url, $current_url);

        // log flights url to file on each run
        $flights_file = fopen('logs/url_captures/flights.txt', 'w');
        fwrite($flights_file, "Flights Exit Unit Url: $current_url
        
");
        fclose($flights_file);

        // Get tab count
        $tab_count = $this->findByXpath('//*[@id="ct-header"]/ul')->getAttribute('data-tab-count');
        $this->assertEquals('7', $tab_count);

        // call to function to loop thru the tabs
        $this->tab_captures($tab_count, $screen_capture_loc);

        // verify all tabs have been clicked
        $this->wd->switchTo($parent_window);
        $untouched_tabs = count($this->wd->findElements(WebDriverBy::xpath("//li[contains(@class, 'visited')]")));
        $this->assertEquals($tab_count, $untouched_tabs);

    }

    function tab_captures($tab_count, $screen_capture_loc) {
        for($i = 1, $l = $tab_count; $i <= $l; ++$i) {
            $window_handles = $this->wd->getWindowHandles();
            $this->wd->switchTo()->window(end($window_handles));

            $tab_element = "(//a[@data-tab-position='$i'])[1]";
            $this->findByXpath($tab_element)->click();

            $this->wd->takeScreenshot($screen_capture_loc . 'exit_unit_tab_' . $i . '.png');

            if (count($this->wd->findElements(WebDriverBy::xpath("//*[@data-tab-position='$i']/a[contains(text(), 'View Now')]"))) === 1) {

                $window_handles = $this->wd->getWindowHandles();
                $this->wd->switchTo()->window(end($window_handles));
                $current_url = $this->wd->getCurrentURL();


                $this->wd->takeScreenshot($screen_capture_loc . 'exit_unit_tab_'. $i . '_redirect.png');

                $this->wd->close();
            }
        }
    }


}
?>