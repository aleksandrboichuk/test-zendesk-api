# Test task
### Assignment:

   Install composer (package manager). Using composer, install the Guzzle library.
Connect autoloader through composer.
Write a script using OOP principles that will store all Tickets (provided you have more than 10,000 tickets) using an API (use the Guzzle library) from the Zendesk API, as well as their main entities. The result should be the recording of these tickets in a csv fil

### Done:
    
- Configured (and successfully started) 2 Docker containers:
   + **php**. Inside the container, _php8.1-fpm_ (using the image), the package manager _composer_, the version control system _git_, and the code debugging tool _xDebug_ were installed.
   + **nginx**. Installed _nginx_ using the _nginx:latest_ image from DockerHub
- Installed **Guzzle** library in php container using composer package manager and configured autoload in composer.json file.
- In the _src_ directory, according to OOP principles, all the necessary classes and their methods have been created to perform the assigned task.
- In the file `index.php`, `autoload.php` is imported and the main class _App_ is initialized. With the help of its method, the execution of all the necessary actions to generate and download a csv file with the necessary data begins.

To execute the script, you need to raise the containers and go to the link http://localhost/index.php. After executing the script, the file _tickets.csv_ will be automatically downloaded, which will contain the result of its execution.
