# Job Scooper v4
## Get automatic email alerts for all new job postings from 70+ job boards and career websites based on your keywords and location.

![Example: Job Scooper Email Notification on Mobile](http://www.bryanselner.com/www-root-wpblog/wp-content/uploads/2014/07/JobScooperResultEmailMobile-250pxw.png "Example: Job Scooper Email Notification on Mobile")

Jobs Scooper currently **supports [over 70 different sites](../../wiki/Job-Sites-Supported-by-Jobs-Scooper)**, such Monster, Indeed, Facebook & ZipRecruiter.  [view all supported sites](../../wiki/Job-Sites-Supported-by-Jobs-Scooper) to see the full list.

## Configuring Search Terms for a User
Make a copy of the [example_config.ini](examples/example_config.ini) and edit it's settings to match the search keywords and locations that you want for that user:
```INI
[global_search_options]
search_location[] = "Seattle, WA"

[search_keyword_set]
[search_keyword_set.analytics]
keywords=["analytics manager", "Digital market", "director"]   ;# will pick up analytics manager and senior/sr analytics manager

```

Then update config.ini values for your notification email address and output folder path.  That's it!  [Run Jobs Scooper](../wiki/Running-Jobs-Scooper) and let it do the work for you.

##Power Up Your Results!
If you're looking at job listings across many sites, Job Scooper has some built-in features to make that work much easier:
* **Automatic duplication detection:**  if the same job is posted on multiple sites, job scooper automatically marks all but the first one as duplicates so you don't waste time reviewing the same job again.
* **Automatically filtered to match titles by your keywords:**  The majority of sites do not support filtering your search to match only the job title.  One of the best features of Job Scooper is that it let's you filter to title-only matches for any site, regardless of whether the site supports it or not!
* **Exclude specific companies automatically**
* **Exclude particular job title matches automatically**

That's just the start of [what Jobs Scooper can do...](../../wiki).

## Running Jobs_Scooper
To run Jobs Scooper, first set an environment variable named JOBSCOOPER_OUTPUT to your output directory on disk.

Then type:
```
/usr/bin/php runJobs.php -all -days 3 -ini myconfig.ini
```

```bash
Required Parameters:

-ini : Path to your configuration ini file (see examples/example_config.ini)
-days X:  number of days before today to download listings for.
-all:  run all the searches found in the .ini file.  Alternatively, you can specify the name of a single job site to run only that site's searches.  e.g. ``-amazon``
```

## Requirements: 
* PHP 5.6.24 
* Python 2.7
* pip:  To install the other required Python modules, run "pip install -r ./python/pyJobNormalizer/requirements.txt"
* NTLK for Python:  You will also need the NTLK data available at http://www.nltk.org/data.html.
* Selenium server standalone:  required for dynamic/client-side websites.  
 * You can configure the app to run selenium locally or pointing to a Selenium instance running on another host or in Docker.  Check out scripts/start_selenium.sh for an example. 
 * To run as part of app:  download [selenium-server-standalone-3.0.1.jar](http://selenium-release.storage.googleapis.com/index.html?path=3.0.1) and copy it to the /lib directory. Oracle](http://www.oracle.com/technetwork/java/javase/downloads/jdk8-downloads-2133151.html)  Selenium Standalone Server requires Java 8 on macOS 10.12. 


## What's New in JobScooper V4
### Job Site Plugin Authoring Using Agenty's Data Scraping Studio  ✏️ 
Non-developers can now author plugins using [Data Scraping Studio](https://www.agenty.com/data-extraction-software.aspx) or the [Advanced Web Scraper Chrome extension](https://chrome.google.com/webstore/detail/agenty-advanced-web-scrap/gpolcofcjjiooogejfbaamdgmgfehgff?hl=en-US) from (http://www.agenty.com)[Agenty].  

Just tag the specific job results page fields for the site and set the Agenty field names to match the corresponding Job Scooper field: 
```php
(
        'TotalResultPageCount',
	'NoPostsFound',
	'TotalPostCount',
	'JobPostItem',
	'NextButton',
	'JobSitePostId',
	'Title',
	'Url',
	'Department',
	'Location',
	'Category',
	'Company',
	'PostedAt',
	'EmploymentType',
);
```

Once you've tagged the fields that are available, export the Agenty data scraping agent configuration you authored out to a JSON file.  In that JSON file, add a key/value under Pagination called "Type" with a value equal to the corresponding Job Scooper value for the job sites pagination and load more results style.

```javascript
"Pagination": {
    "Type": "INFINITE-SCROLL-NO-CONTROL"
  }
```

The currently list of pagination types supported by Job Scooper can be found in SitePlugins.php. 

Save your updated Agenty JSON config file to the plugins/JsonBased directory and kick off a job scooper run.  Your new Agenty-authored plugin will run exactly like any other plugin built for Job Scooper. 

### Don't Know PHP?  Add a plugin solely via JSON instead!  ✏️ 
You can define the full configuration for a job site plugin in a single JSON file.  Just drop the new file into the plugins/JsonBased folder and let it rip.  

```javascript
{
  "JobSiteName": "Startjobs",
  "SourceURL": "https://start.jobs/search",
  "Collections": [
    {
      "Name": "PageFields",
      "Fields": [
      {
          "Name": "JobPostItem",
          "Selector": ".js-job",
          "Attribute": "node",
          "Type": "CSS"
        },
        {
          "Name": "pages_count",
          "Selector": "div.js-infinite-scroll",
          "Attribute": "data-total-pages",
          "Type": "CSS"
        }
      ]
    },
    {
      "Name": "ItemFields",
      "Fields": [
        {
          "Name": "Title",
          "Selector": ".title",
          "Attribute": "text",
          "Type": "CSS"
        },
        {
          "Name": "Location",
          "Selector": "span.location",
          "Attribute": "text",
          "Type": "CSS"
        }
      }
    }
  ],
  "Pagination": {
    "Type": "INFINITE-SCROLL-NO-CONTROL"
  }
}
```

### Job Scooper now Supports Running under Docker 🖥 
Setting up and running job scooper anywhere is now made easier through Docker.  With just a few tweaks to the Dockerfile and associated run scripts, you can have Job Scooper quickly up and running in a container quickly.

Check out the Dockerfile and build_and_run_docker.* files in the repo for a set of base files that should get you 90% of the way there for your system.

Job Scooper also now supports running Selenium for AJAX job sites in a Docker container. 


### Plugins Are Even Now Easier to Author in PHP ✏️ 
Using the new Simple Job Site base plugin classes, developers can now add an entire new plugin for a job site in fewer than 40 lines of code!  Here's a fully-featured job site plugin example of how to do it:

 ```php
class PluginCyberJobs extends AjaxHtmlSimplePlugin
{
    protected $JobSiteName = 'CyberJobs';
    protected $JobPostingBaseUrl = "https://cyber.jobs";
    protected $SearchUrlFormat = "https://cyber.jobs/search/?page=***PAGE_NUMBER***&searchterms=***KEYWORDS***&searchlocation=***LOCATION***&newsearch=true&originalsearch=true&sorttype=date";

    protected $PaginationType = C__PAGINATION_PAGE_VIA_URL;
    protected $LocationType = 'location-city-comma-statecode';

    protected $arrListingTagSetup = array(
        'TotalPostCount' =>  array('tag' => 'span', 'attribute' => 'id', 'attribute_value' =>'total-result-count', 'return_attribute' => 'text', 'return_value_regex' => '/.*?(\d+).*?/'),
        'JobPostItem' => array('selector' => '.job-details-container'),
        'title' =>  array(array('selector' => 'div.job-title'), array('tag' => 'a'), 'return_attribute' => 'text'),
        'link' =>  array(array('selector' => 'div.job-title'), array('tag' => 'a'), 'return_attribute' => 'href'),
        'job_id' =>  array(array('selector' => 'div.job-title'), array('tag' => 'a'), 'return_attribute' => 'href', 'return_value_regex' =>'/.*?(\d+)$/'),
        'employment_type' =>  array(array('tag' => 'div', 'attribute' => 'class', 'attribute_value' =>'wage'), array('tag' => 'span')),
        'location' =>  array('tag' => 'div', 'attribute' => 'class', 'attribute_value' =>'location'),
        'job_posting_date' =>  array('tag' => 'div', 'attribute' => 'class', 'attribute_value' =>'posted')
    );

}
```

**Get the full set of feature additions and updates in the [release notes](../../releases).**

## Other Stuff
* Version:  v4.0.beta1 [release notes](../../releases)
* Author:  Bryan Selner (dev at recoilvelocity dot com)
* Tested mainly in Linux under Docker (see Dockerfile) and macOS 12.12.  Your mileage might vary on other platforms.
* Issues/Bugs:  [Please report them!](../../issues)
