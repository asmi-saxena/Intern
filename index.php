<?php
session_start();
include("api/connect.php");

// Initialize form data
$formData = [
    'firstName' => '',
    'lastName' => '',
    'jobTitle' => '',
    'email' => '',
    'company' => '',
    'employees' => '',
    'country' => '',
    'phone' => ''
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreamforce 2025 Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="min-h-screen flex flex-col">
    <div class="flex-grow">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-8">
            <div class="space-y-6">
                <img src='https://www.salesforce.com/content/dam/web/en_au/www/images/events/dreamforce/df-logo-dark.png' alt='dreamforce logo' class="w-48">
                <h1 class='text-4xl font-bold text-blue-900'>Calling all Trailblazers. Dreamforce 2025 is coming.</h1>
                <p class="text-xl">Fall 2025 | San Francisco & Salesforce+</p>
                <img src="https://www.salesforce.com/content/dam/web/en_us/www/images/form/dreamforce/df25-save-the-date.png" alt="Dreamforce" class="w-full rounded-lg shadow-lg">
                <p class="text-lg">Be the first to know when registration for Dreamforce 2025 opens. Get ready to mark your calendar for an epic celebration of customer success and the most impactful event for your business.</p>
                <p class="text-lg">Join the list now to get the lowest price when registration opens. Plus, be the first to know all the big announcements, get first access to the best hotels, and so much more. We hope you join us for #DF25 to learn, connect, have fun, and give back.</p>
            </div>
            
            <div class="bg-gray-100 p-8 rounded-lg shadow-md m-38 mb-80">
                <h2 class="text-2xl font-bold mb-6">Share a few details.</h2>
                <form id="registrationForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <input
                            type="text"
                            name="firstName"
                            placeholder="First name"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                        <input
                            type="text"
                            name="lastName"
                            placeholder="Last name"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <input
                            type="text"
                            name="jobTitle"
                            placeholder="Job title"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                        <input
                            type="email"
                            name="email"
                            placeholder="Email"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <input
                            type="text"
                            name="company"
                            placeholder="Company"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                        <select
                            name="employees"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Employees</option>
                            <option value="1">1 - 20 employees</option>
                            <option value="2">21 - 50 employees</option>
                            <option value="3">51 - 200 employees</option>
                            <option value="4">201+ employees</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <input
                            type="tel"
                            name="phone"
                            placeholder="Phone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                        <select
                            name="country"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="India">India</option>
                            <option value="United States">United States</option>
                            <option value="United Kingdom">United Kingdom</option>
                            <option value="Australia">Australia</option>
                        </select>
                    </div>
                    <p class="text-sm text-gray-600">
                        By registering, you confirm that you agree to the
                        <a href="#" class="text-blue-600 hover:underline">Event Terms of Service</a> and
                        to the storing and processing of your personal data by Salesforce as
                        described in the <a href="#" class="text-blue-600 hover:underline">Privacy Statement</a>.
                    </p>
                    <button
                        type="submit"
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        JOIN THE LIST
                    </button>
                </form>
                <button
                    onclick="window.location.href='components/login.php'"
                    class="w-full mt-4 bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    LOGIN
                </button>
            </div>
        </div>
    </div>
    <footer class="bg-blue-900 text-white py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap justify-between items-center mb-4">
                <div class="flex items-center space-x-2 mb-4 md:mb-0">
                    <span class="font-bold">Worldwide</span>
                    <span>&#9660;</span>
                </div>
                <nav class="space-x-4 text-sm flex flex-wrap">
                    <a href="#" class="hover:underline mb-2">Legal</a>
                    <a href="#" class="hover:underline mb-2">Terms of Service</a>
                    <a href="#" class="hover:underline mb-2">Privacy Information</a>
                    <a href="#" class="hover:underline mb-2">Responsible Disclosure</a>
                    <a href="#" class="hover:underline mb-2">Trust</a>
                    <a href="#" class="hover:underline mb-2">Contact</a>
                    <a href="#" class="hover:underline mb-2">Cookie Preferences</a>
                    <a href="#" class="hover:underline mb-2">Your Privacy Choices</a>
                </nav>
            </div>
            <!-- <div class="text-sm">
                <p>&copy; Copyright 2024 Salesforce, Inc. <a href="#" class="underline">All rights reserved</a>. Various trademarks held by their respective owners.</p>
                <p class="mt-2">Salesforce, Inc. Salesforce Tower, 415 Mission Street, 3rd Floor, San Francisco, CA 94105, United States</p>
            </div> -->
        </div>
    </footer>

    <script>
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            axios.post('api/insert_registrations.php', formData)
                .then(function (response) {
                    alert('Registration successful!');
                    document.getElementById('registrationForm').reset();
                })
                .catch(function (error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
        });
    </script>
</body>
</html>