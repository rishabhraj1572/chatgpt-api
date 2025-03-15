# Use official PHP image
FROM php:8.2-cli

# Set working directory
WORKDIR /var/www/html

# Copy project files into the container
COPY . .

# Expose the port (Render uses 10000)
EXPOSE 10000

# Start PHP's built-in server
CMD ["php", "-S", "0.0.0.0:10000"]
