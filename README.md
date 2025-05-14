<<<<<<< HEAD
# USSD Bus Tracking System

This system provides a USSD interface for bus tracking, seat booking, and SMS notifications using Africa's Talking platform.

## Features

1. **User Registration**: New users must register before accessing the system
2. **Track a Bus**: Check the current location and ETA of a bus
3. **Check Seat Availability**: See how many seats are available on a specific bus
4. **Bus Schedule**: View the schedule of all buses with filtering options
5. **Book a Seat**: Book a seat on a bus
6. **Cancel Booking**: Cancel an existing booking
7. **View SMS History**: View the history of SMS messages sent to your phone
8. **Back Button**: Every action has a back button to return to the main menu

## Setup

1. Configure your Africa's Talking API credentials in `sms.php`
2. Set up the USSD callback URL in your Africa's Talking account to point to `ussd.php`

## Files

- `ussd.php`: Main USSD application
- `sms.php`: SMS sending functionality
 


## Usage

When a user dials the USSD code for the first time, they will be prompted to register. After registration, they will see the main menu with options. Each option has a back button (option 0) to return to the main menu.

### User Registration

New users must register before they can access the system. The registration process collects:
1. Full name
2. Email address (optional)

After registration, users receive a welcome SMS and can immediately access all features.

### SMS Notifications

The system sends detailed SMS notifications for various actions:

1. **Bus Tracking**: When you track a bus, you receive an SMS with the current location, ETA, and available seats
2. **Seat Availability**: When you check seat availability, you receive an SMS with seat information and booking recommendations
3. **Bus Schedule**: You can request the complete bus schedule to be sent via SMS
4. **Booking Confirmation**: When you book a seat, you receive a detailed booking confirmation SMS with bus information
5. **Booking Cancellation**: When you cancel a booking, you receive a cancellation confirmation SMS
6. **Follow-up Messages**: The system sends follow-up messages with helpful information and prompts

### SMS History

The system keeps track of all SMS messages sent to users. Users can view their SMS history through the USSD menu by selecting option 6.

If you're seeing "No SMS history found" when trying to view SMS history, you can:

1. Perform actions that generate SMS messages (like booking a seat)
2. Use the `add_test_sms.php` tool to manually add test messages
3. The system will automatically add welcome messages the first time you view SMS history



### Back Buttons

Every action in the system has a back button (option 0) that allows users to return to the main menu without completing the current action.
=======
# group-3-22rp01692-22RP01917-22rp03334-BusTrack-USSD
>>>>>>> c64f02a54d3abc2a60d551cc01caf6f5f00ff248
