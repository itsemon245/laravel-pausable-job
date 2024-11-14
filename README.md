![Laravel_pausable_job_banner](https://github.com/itsemon245/laravel-pausable-job/assets/82655944/a9e055c9-9610-4d4e-94d4-ecc61acfd09b)

<p align="center">
 <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/itsemon245/laravel-pausable-job?style=for-the-badge&label=Downloads&color=61C9A8" alt="Total Downloads"></a>
 <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/itsemon245/laravel-pausable-job?style=for-the-badge&label=Version" alt="Latest Stable Version"></a>
 <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/itsemon245/laravel-pausable-job?style=for-the-badge&label=License" alt="License"></a>
</p>

# Laravel Pausable Job
This package allows your laravel jobs to be **pausable** & **resumeable** by any model in your application on runtime.

> [!NOTE]
> **Currently it only supports Laravel's Default Database Connection for Queues**

### The Use Case

Imagine you have an Email Marketing Application where your clients can create Campaigns and send Emails to their Subscribers.
A campaign may have thousands of email in it and you want add a feature so your clients can simply pause the campaign and the email will be paused realtime.

Since campaign emails will likely to be processed by the queue using a job we can no longer pause the queue or the jobs for this specific campaign because they have already been dispatched.\
That's where this package comes into play. You can simply pause all the jobs related to a perticular model whenever you want.

### Installation
```bash
composer require itsemon245/laravel-pausable-job
```

### Publish Migration
```bash
php artisan vendor:publish --provider="Itsemon245\PausableJob\PausableJobServiceProvider"
php artisan migrate
```

### Usage
1. Use the `Pausable` trait in the job that you want to make pausable
```php
//Other imports
 use Itsemon245\PausableJob\Traits\Pausable;

class EmailJob implements ShouldQueue
{
  // Other Traits
   use Pausable;

    public function __construct(public Campaign $campaign)
    {
        /**
         * Set or bind which model is responsible to pause the job
         */
        $this->setPausedBy($campaign);
    }
}
```
2. Use the `HasPausableJobs` trait in the model that you want to use to pause the jobs
```php
//Other imports
 use Itsemon245\PausableJob\Traits\HasPausableJobs;

class Campaign extends Model
{
    //Other traits
   use HasPausableJobs;
}
```
3. After using the `HasPausableJobs` trait your model should have access to the `resumeJobs()` method to resume the jobs again.
You can now pause and resume the jobs by following this example in your controllers.
```php
class CampaignController extends Controller{

  public function pause(Campaign $campaign){
    //Immediately pause all jobs that are related to this campaign 
    $campaign->pauseJobs();
  
    return back();
  }
  
  public function resume(Campaign $campaign){
  // Resume jobs whenever you want
   $campaign->resumeJobs();
  }

}
```

And with that you now have the ability to pause and resume any job on the fly as long as it relates to a model.

> [!NOTE]
> Thank you for considering the package.\
> This is my first package so feel free to critisize my mistakes & help me overcome them.\
> And a star would be higly appreciated and will help increase my motivation.

**If you have any suggestion please don't hasitate.**

### Thanks Again & Happy Coding
