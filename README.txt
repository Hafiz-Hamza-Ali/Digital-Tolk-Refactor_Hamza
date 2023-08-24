//What i have done 
1. Readme described above (point X above) + refactored code


Code improvement what i have done:-

        Add all business logic in BookingController and interaction with DB in BookingRepository.

        Example:- 
        Method:-updateJobAttributes
        Method:-updateDistance
        In Both of the above method DB Query is written so i make two new methods and move that logic into BookingRepository.

Big Method Convert into Small methods that is resuable.

        Example:-
        BookingRepository

        Method:-store  
        In store method very large number of code written so i convert into small chunks nd made these methods.

        BookingRepository
        Method:-validateBookingData
        Method:-setGenderAndCertified
        Method:-getJobType
        Method:-getJobForArray

Exception Handling:
        In Exception Handling we use try and catch block so we easily handle exception.

        Example:-
        BookingController:distanceFeed

Oop Usage :-
        parent constructor use for Job Model that set in base repository so we easily call with $this->method.Use private function as well.
        Example:-  function __construct(Job $model, MailerInterface $mailer).

//What is Better For Code Improvement 
These point is necessary for code Refactor

        Code Reusability:
        By encapsulating data access logic within repositories, you can reuse this logic across different parts of your application. This reduces code duplication and 
        promotes a DRY (Don't Repeat Yourself) coding style.

        Database-Logic: 
        In Controller Code  business logic without involving the actual database, making tests faster and more reliable.Database logic we are written in repository 
        and use $this->model as a parent constructor.

        Separation of Concerns:
        The Repository Pattern separates data access concerns from business logic. This separation enhances the overall maintainability of your codebase as it's easier 
        to make changes or optimizations to data access code without affecting the application's core functionality.

        Eloquent Integration:
        Laravel's Eloquent ORM is a powerful tool for database interaction. Repositories can wrap Eloquent queries, making it easier to manage complex queries, 
        apply filters, and enforce security checks within the repository layer.

        Improved Code Readability:
        By using repositories, you create a clear and consistent way of accessing data throughout your application. This improves code readability as developers can easily 
        understand where and how data is being fetched and manipulated.

