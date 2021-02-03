<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\{
    State,
    City,
    User,
    Profession,
    ProviderDetails,
    ArtistUser,
    Job,
    Subscription,
    ArtistDetails,
    AppliedJobs,
    ArtistSubscription,
    PaidUsers,
    Feedback,
    Refer,
    ArtistPorfolio
};

use App\Repositories\Contracts\{
    StateRepository,
    CityRepository,
    UserRepository,
    ProfessionRepository,
    ProviderDetailsRepository,
    ArtistUserRepository,
    JobRepository,
    SubscriptionRepository,
    ArtistDetailsRepository,
    AppliedJobsRepository,
    ArtistSubscriptionRepository,
    PaidUsersRepository,
    FeedbackRepository,
    ReferRepository,
    ArtistPorfolioRepository
};

use App\Repositories\Eloquent\{
    EloquentStateRepository,
    EloquentCityRepository,
    EloquentUserRepository,
    EloquentProfessionRepository,
    EloquentProviderDetailsRepository,
    EloquentArtistUserRepository,
    EloquentJobRepository,
    EloquentSubscriptionRepository,
    EloquentArtistDetailsRepository,
    EloquentAppliedJobsRepository,
    EloquentPaidUsersRepository,
    EloquentFeedbackRepository,
    EloquentReferRepository,
    EloquentArtistPorfolioRepository
};

class RepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(StateRepository::class, function () {
            return new EloquentStateRepository(new State());
        });
        
        $this->app->bind(CityRepository::class, function () {
            return new EloquentCityRepository(new City());
        });

        $this->app->bind(UserRepository::class, function () {
            return new EloquentUserRepository(new User());
        });

        $this->app->bind(ProfessionRepository::class, function () {
            return new EloquentProfessionRepository(new Profession());
        });

        $this->app->bind(ProviderDetailsRepository::class, function () {
            return new EloquentProviderDetailsRepository(new ProviderDetails());
        });

        $this->app->bind(ArtistUserRepository::class, function () {
            return new EloquentArtistUserRepository(new ArtistUser());
        });

        $this->app->bind(JobRepository::class, function () {
            return new EloquentJobRepository(new Job());
        });

        $this->app->bind(SubscriptionRepository::class, function () {
            return new EloquentSubscriptionRepository(new Subscription());
        });

        $this->app->bind(ArtistDetailsRepository::class, function () {
            return new EloquentArtistDetailsRepository(new ArtistDetails());
        });

        $this->app->bind(AppliedJobsRepository::class, function () {
            return new EloquentAppliedJobsRepository(new AppliedJobs());
        });

        $this->app->bind(ArtistSubscriptionRepository::class, function () {
            return new EloquentArtistSubscriptionRepository(new ArtistSubscription());
        });

        $this->app->bind(PaidUsersRepository::class, function () {
            return new EloquentPaidUsersRepository(new PaidUsers());
        });

        $this->app->bind(FeedbackRepository::class, function () {
            return new EloquentFeedbackRepository(new Feedback());
        });

        $this->app->bind(ReferRepository::class, function () {
            return new EloquentReferRepository(new Refer());
        });

        $this->app->bind(ArtistPorfolioRepository::class, function () {
            return new EloquentArtistPorfolioRepository(new Refer());
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        return [
            StateRepository::class,
            CityRepository::class,
            UserRepository::class,
            ProfessionRepository::class,
            ProviderDetails::class,
            ArtistUser::class,
            Job::class,
            Subscription::class,
            ArtistDetails::class,
            AppliedJobs::class,
            ArtistSubscription::class,
            PaidUsers::class,
            Feedback::class,
            Refer::class,
            ArtistPorfolio::class
        ];
    }
}
