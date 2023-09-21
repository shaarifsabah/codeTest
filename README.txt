Questions:1 A readme with:   Your thoughts about the code. What makes it amazing code. Or what makes it ok code. Or what makes it terrible code. How would you have done it. Thoughts on formatting, structure, logic.. The more details that you can provide about the code (what's terrible about it or/and what is good about it) the easier for us to assess your coding style, mentality etc
Answer:
Actually according to my knowledge there must be a service between controller and repo where our business logic should be written because
our controller should be precise and I think it's not implementing the complete SOLID principle and not even Repository patter.
Points to make it OK code.
-Repositories should be refactored and the date should be in their own services we just have to use the Repository for model related work.
-Repositories should be bind with their contracts in the register method of your providers for single object via singleton method.
-Controller should use validation if its using for web or api if for web then use custom form requests classes.
-The code is written very massively no naming convention has been used and no PSR's has been followed inside and no structured programming
technique has been used.
-Function must be used to separate and structured them in a good way.
-Logic is not bad in the code but it's very messy code to understand for others.
And 

Question:2 Refactor it if you feel it needs refactoring. The more love you put into it. The easier for us to asses your thoughts, code principles etc
Answer:
I have refactor it but if I was a developer I must inject a service between controller and Repository.
I must use middleware for logging instead of injecting the logger in every repository and wrap the whole web file with the middleware in Route service provider.
I basically use Repo pattern in which route->controller->service->repository->model->return to view;
I have also worked with facade pattern but that's not mainly used and other developer's dont have more knowledge about it so I didn't pursue that.



