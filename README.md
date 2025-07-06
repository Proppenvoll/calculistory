# Calculistory: A Simple PHP Calculator with History

Calculistory is a lightweight, dependency-free, dual interface web application that provides a simple calculator with a persistent history.

This educational project is built using only vanilla PHP, HTML, and CSS, with no external libraries or frameworks.

## Getting Started

1. Start the project using Docker/Podman Compose:

   ```
   podman compose up
   ```

2. Connect to the running app container:

   ```
   podman exec -it calculistory-dev /bin/bash
   ```

3. Run the development server:

   ```
   php -S [::0]:8000
   ```
