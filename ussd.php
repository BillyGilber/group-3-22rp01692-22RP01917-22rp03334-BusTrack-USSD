<?php
// Connect to DB


$conn = new mysqli("localhost", "root", "", "ussd_bus_system");
if ($conn->connect_error) {
    die("END DB connection failed.");
}

// Add message_type column if it doesn't exist
$checkColumn = $conn->query("SHOW COLUMNS FROM sms_history LIKE 'message_type'");
if ($checkColumn->num_rows === 0) {
    $conn->query("ALTER TABLE sms_history ADD COLUMN message_type ENUM('sent', 'received') DEFAULT 'sent'");
    // Update existing records - assume they were sent messages
    $conn->query("UPDATE sms_history SET message_type = 'sent' WHERE message_type IS NULL");
}

require_once 'sms.php'; 

// USSD handling for Africa's Talking platform
$sessionId   = $_POST["sessionId"] ?? '';
$serviceCode = $_POST["serviceCode"] ?? '';
$phoneNumber = $_POST["phoneNumber"] ?? '';
$text        = $_POST["text"] ?? '';

// Initialize SMS handler with the user's phone number
$smsHandler = new Sms($phoneNumber);

// Log USSD request for debugging
$logFile = fopen("ussd_log.txt", "a");
fwrite($logFile, date("Y-m-d H:i:s") . " - Phone: $phoneNumber, Text: $text\n");
fclose($logFile);

// Function to get SMS history for a phone number
function getSMSHistoryText($phoneNumber, $conn) {
    $stmt = $conn->prepare("SELECT message, sent_at FROM sms_history WHERE phone_number = ? ORDER BY sent_at DESC LIMIT 5");
    $stmt->bind_param("s", $phoneNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $historyText = "";
    $count = 1;
    
    while ($row = $result->fetch_assoc()) {
        $date = date("d/m/Y H:i", strtotime($row['sent_at']));
        $message = substr($row['message'], 0, 30) . (strlen($row['message']) > 30 ? "..." : "");
        $historyText .= "$count. $message ($date)\n";
        $count++;
    }
    
    if (empty($historyText)) {
        $historyText = "No SMS history found.";
    }
    
    return $historyText;
}

// Function to check if user is registered
function isUserRegistered($phoneNumber, $conn) {
    // Normalize phone number for consistent checking
    $phoneNumber = normalizePhoneNumber($phoneNumber);
    
    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE phone_number = ?");
    $stmt->bind_param("s", $phoneNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
        $stmt->close();
        return ['registered' => true, 'name' => $userData['full_name']];
    }
    
    $stmt->close();
    return ['registered' => false, 'name' => ''];
}

// Function to normalize phone number format
function normalizePhoneNumber($phone) {
    // Remove any non-digit characters except the + sign
    $phone = preg_replace('/[^\d+]/', '', $phone);
    
    // If it starts with 0, replace with +250 (Rwanda)
    if (substr($phone, 0, 1) == '0') {
        $phone = '+250' . substr($phone, 1);
    }
    
    // If it starts with 7 and is 9 digits, add +250 (Rwanda)
    if (strlen($phone) === 9 && substr($phone, 0, 1) == '7') {
        $phone = '+250' . $phone;
    }
    
    // If it doesn't have a + prefix but starts with country code
    if (substr($phone, 0, 1) != '+' && substr($phone, 0, 3) == '250') {
        $phone = '+' . $phone;
    }
    
    // Validate Rwanda phone number format
    if (!preg_match('/^\+250[237]\d{8}$/', $phone)) {
        error_log("Invalid Rwanda phone number format: $phone");
    }
    
    return $phone;
}

// Function to get user's language preference
function getUserLanguage($phoneNumber, $conn) {
    $stmt = $conn->prepare("SELECT language_preference FROM users WHERE phone_number = ?");
    $stmt->bind_param("s", $phoneNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['language_preference'];
    }
    return 'rw'; // Default to Kinyarwanda
}

// Function to get menu text based on language
function getMenuText($key, $lang = 'rw') {
    $menuText = [
        'welcome' => [
            'en' => 'Welcome to SmartBus',
            'rw' => 'Murakaza neza kuri SmartBus'
        ],
        'track_bus' => [
            'en' => 'Track a Bus',
            'rw' => 'Kureba aho imodoka igeze'
        ],
        'check_seats' => [
            'en' => 'Check Seat Availability',
            'rw' => 'Kureba imyanya ihari'
        ],
        'schedule' => [
            'en' => 'Bus Schedule',
            'rw' => "Gahunda y'Ingendo"
        ],
        'book_seat' => [
            'en' => 'Book a Seat',
            'rw' => 'Gufata Itike'
        ],
        'cancel_booking' => [
            'en' => 'Cancel Booking',
            'rw' => 'Guhagarika Itike'
        ],
        'view_sms' => [
            'en' => 'View SMS History',
            'rw' => 'Kureba Ubutumwa'
        ],
        'exit' => [
            'en' => 'Exit',
            'rw' => 'Gusohoka'
        ],
        'register' => [
            'en' => 'Register Now',
            'rw' => 'Iyandikishe'
        ],
        'back' => [
            'en' => 'Back to Main Menu',
            'rw' => 'Subira inyuma'
        ]
    ];
    return $menuText[$key][$lang] ?? $menuText[$key]['rw'];
}

// Function to generate the main menu text based on registration status
function getMainMenu($isRegistered, $userName = '') {
    if ($isRegistered) {
        $menu  = "CON Welcome to SmartBus" . (!empty($userName) ? ", " . $userName : "") . "!\n";
        $menu .= "1. Track a Bus\n";
        $menu .= "2. Check Seat Availability\n";
        $menu .= "3. Bus Schedule\n";
        $menu .= "4. Book a Seat\n";
        $menu .= "5. Cancel Booking\n";
        $menu .= "6. View SMS History\n";
        $menu .= "7. Exit";
    } else {
        $menu  = "CON Welcome to SmartBus!\n";
        $menu .= "You need to register before using our services.\n";
        $menu .= "1. Register Now\n";
        $menu .= "2. Exit";
    }
    return $menu;
}

// Function to send SMS
function sendSMSMessage($phone, $message) {
    try {
        $sms = new Sms($phone);
        $result = $sms->sendSMS($message, $phone);
        
        // Store in SMS history with message_type
        global $conn;
        $stmt = $conn->prepare("INSERT INTO sms_history (phone_number, message, sent_at, message_type) VALUES (?, ?, NOW(), 'sent')");
        $stmt->bind_param("ss", $phone, $message);
        $stmt->execute();
        
        return $result;
    } catch (Exception $e) {
        error_log("SMS Error: " . $e->getMessage());
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

// Function to store received SMS
function storeReceivedSMS($phone, $message) {
    try {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO sms_history (phone_number, message, sent_at, message_type) VALUES (?, ?, NOW(), 'received')");
        $stmt->bind_param("ss", $phone, $message);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Store Received SMS Error: " . $e->getMessage());
        return false;
    }
}

// Process USSD request
$level = explode("*", $text);

// Normalize phone number
$phoneNumber = normalizePhoneNumber($phoneNumber);

// Check if user is registered
$userData = isUserRegistered($phoneNumber, $conn);
$userRegistered = $userData['registered'];
$userName = $userData['name'];

// Log the registration status for debugging
error_log("USSD: Phone $phoneNumber, Registered: " . ($userRegistered ? "Yes" : "No"));

if ($text == "") {
    // Show main menu based on registration status
    $response = getMainMenu($userRegistered, $userName);
}
// Handle registration process
else if (!$userRegistered && $level[0] == "1") {
    if (!isset($level[1])) {
        // Ask for full name
        $response = "CON Please enter your full name:";
    } else if (!isset($level[2])) {
        // Ask for email
        $response = "CON Please enter your email (or type 'skip' to skip):";
    } else {
        // Process registration
        $fullName = trim($level[1]);
        $email = trim($level[2]);
        
        if (strlen($fullName) < 3) {
            $response = "END Registration failed. Name is too short. Please try again with your full name.";
        } else {
            if (strtolower($email) == 'skip') {
                $email = '';
            }
            
            // Insert new user with English language preference
            $stmt = $conn->prepare("INSERT INTO users (phone_number, full_name, email, language_preference) VALUES (?, ?, ?, 'en')");
            $stmt->bind_param("sss", $phoneNumber, $fullName, $email);
            
            if ($stmt->execute()) {
                $welcomeMsg = "Welcome to SmartBus, $fullName! Your registration is complete. Dial *123# to access our services anytime.";
                sendSMSMessage($phoneNumber, $welcomeMsg);
                
                // Send tips SMS
                $tipMsg = "SmartBus Tips:\n1. Track buses in real-time\n2. Book seats in advance\n3. Check your SMS history\n4. Cancel bookings if plans change";
                sendSMSMessage($phoneNumber, $tipMsg);
                
                $response = "CON Registration successful!\n\n" . substr(getMainMenu(true, $fullName), 4);
            } else {
                $response = "END Registration failed. Please try again later.";
            }
        }
    }
}
// Exit option from registration menu
else if (!$userRegistered && $level[0] == "2") {
    $response = "END Thank you for considering SmartBus. Dial *123# anytime to register.";
} 
// Option 1: Track a Bus (for registered users)
else if ($userRegistered && $level[0] == "1") {
    if (!isset($level[1])) {
        // Get all available buses
        $query = "SELECT route_id, location, eta, seats_available FROM buses ORDER BY route_id";
        $result = $conn->query($query);
        
        if ($result->num_rows > 0) {
            $response = "CON Available Buses:\n";
            $count = 1;
            while ($row = $result->fetch_assoc()) {
                $response .= "$count. {$row['route_id']} - {$row['location']}\n";
                $response .= "   ETA: {$row['eta']}, Seats: {$row['seats_available']}\n";
                $count++;
            }
            $response .= "\nEnter bus number (1-" . ($count-1) . ") to track:\n";
            $response .= "0. Back to Main Menu";
        } else {
            $response = "END No buses available in the system.";
        }
    } else if ($level[1] == "0") {
        // Back to main menu
        $response = getMainMenu($userRegistered, $userName);
    } else {
        // Get the selected bus number and convert it to the actual bus ID
        $selectedNumber = (int)$level[1];
        
        // Get all buses again to map the selection to a bus ID
        $query = "SELECT route_id, location, eta, seats_available FROM buses ORDER BY route_id";
        $result = $conn->query($query);
        
        if ($result->num_rows > 0) {
            $buses = array();
            while ($row = $result->fetch_assoc()) {
                $buses[] = $row;
            }
            
            // Check if selected number is valid
            if ($selectedNumber > 0 && $selectedNumber <= count($buses)) {
                $selectedBus = $buses[$selectedNumber - 1];
                $busId = $selectedBus['route_id'];
                $location = $selectedBus['location'];
                $eta = $selectedBus['eta'];
                $seatsAvailable = $selectedBus['seats_available'];
                
                // Show tracking information
                $response = "END Tracking Bus $busId:\n";
                $response .= "Current Location: $location\n";
                $response .= "ETA: $eta\n";
                $response .= "Available Seats: $seatsAvailable";
                
                // Prepare detailed SMS
                $trackingTime = date("d/m/Y H:i");
                $msg = "BUS TRACKING DETAILS\n";
                $msg .= "Bus: $busId\n";
                $msg .= "Current Location: $location\n";
                $msg .= "ETA: $eta\n";
                $msg .= "Available Seats: $seatsAvailable\n";
                $msg .= "Tracking Time: $trackingTime\n\n";
                $msg .= "Thank you for using SmartBus.";
                
                // Send tracking SMS
                sendSMSMessage($phoneNumber, $msg);
                
                // Send booking prompt if seats available
                if ($seatsAvailable > 0) {
                    $followupMsg = "Would you like to book a seat on bus $busId? Dial *123# and select option 4 to book your ticket. - SmartBus";
                    sendSMSMessage($phoneNumber, $followupMsg);
                }
            } else {
                $response = "END Invalid selection. Please try again with a valid bus number.";
            }
        } else {
            $response = "END No buses available in the system.";
        }
    }
} 
// Option 2: Check Seat Availability (for registered users)
else if ($userRegistered && $level[0] == "2") {
    if (!isset($level[1])) {
        $response = "CON Enter Bus Plate Number (e.g., RAB 456C):\n";
        $response .= "0. Back to Main Menu";
    } else if ($level[1] == "0") {
        // Back to main menu
        $response = getMainMenu($userRegistered, $userName);
    } else {
        $busId = strtoupper(str_replace(' ', ' ', $level[1]));
        $stmt = $conn->prepare("SELECT seats_available, location, eta FROM buses WHERE route_id = ?");
        $stmt->bind_param("s", $busId);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($seats, $location, $eta);
            $stmt->fetch();
            $response = "END Bus $busId has $seats seats available.";
            
            // Create a detailed seat availability SMS
            $checkTime = date("d/m/Y H:i");
            $msg = "SEAT AVAILABILITY\n";
            $msg .= "Bus: $busId\n";
            $msg .= "Available seats: $seats\n";
            $msg .= "Current location: $location\n";
            $msg .= "ETA: $eta\n";
            $msg .= "Check time: $checkTime\n";
            
            // Add recommendation based on seat availability
            if ($seats > 10) {
                $msg .= "Plenty of seats available. No rush!";
            } else if ($seats > 0) {
                $msg .= "Seats filling up fast. Book now!";
            } else {
                $msg .= "Sorry, this bus is full. Please check other buses.";
            }
            
            // Send the SMS
            sendSMSMessage($phoneNumber, $msg);
            
            // If seats are available, send a booking prompt
            if ($seats > 0) {
                $bookingPrompt = "Want to secure one of the $seats available seats on bus $busId? Dial *123# and select option 4 to book your ticket. - SmartBus";
                sendSMSMessage($phoneNumber, $bookingPrompt);
            }
        } else {
            $response = "END Bus not found. Please try another bus number.";
        }
    }
} 
// Option 3: Bus Schedule (for registered users)
else if ($userRegistered && $level[0] == "3") {
    $query = "SELECT route_id, eta, location, seats_available FROM buses";
    $result = $conn->query($query);
    $scheduleText = "";
    $fullScheduleText = "GAHUNDA Y'INGENDO\n"; // Bus Schedule
    $fullScheduleText .= "Igihe: " . date("d/m/Y H:i") . "\n\n"; // Current time
    
    while ($row = $result->fetch_assoc()) {
        $scheduleText .= $row['route_id'] . ": " . $row['eta'] . "\n";
        $fullScheduleText .= "Imodoka: " . $row['route_id'] . "\n"; // Bus
        $fullScheduleText .= "- Igihe izahagera: " . $row['eta'] . "\n"; // ETA
        $fullScheduleText .= "- Aho iherereye: " . $row['location'] . "\n"; // Location
        $fullScheduleText .= "- Imyanya isigaye: " . $row['seats_available'] . "\n\n"; // Available seats
    }
    
    $response = "CON Gahunda y'Ingendo:\n" . $scheduleText . "\n0. Subira inyuma"; // Bus Schedules, Back to Menu
    
    if (isset($level[1])) {
        if ($level[1] == "0") {
            // Back to main menu
            if ($userRegistered) {
                $response  = "CON Welcome back to SmartBus, " . $userName . "!\n";
                $response .= "1. Track a Bus\n";
                $response .= "2. Check Seat Availability\n";
                $response .= "3. Bus Schedule\n";
                $response .= "4. Book a Seat\n";
                $response .= "5. Cancel Booking\n";
                $response .= "6. View SMS History\n";
                $response .= "7. Exit";
            } else {
                $response  = "CON Welcome to SmartBus!\n";
                $response .= "You need to register before using our services.\n";
                $response .= "1. Register Now\n";
                $response .= "2. Exit";
            }
        } else if ($level[1] == "1") {
            // Send schedule via SMS
            $fullScheduleText .= "Thank you for using SmartBus scheduling service.";
            sendSMSMessage($phoneNumber, $fullScheduleText);
            $response = "END Complete bus schedule has been sent to your phone via SMS.";
        } else if ($level[1] == "2") {
            // Filter by available seats
            if (!isset($level[2])) {
                $response = "CON Enter minimum number of seats needed:";
            } else {
                $minSeats = intval($level[2]);
                $filteredSchedule = "BUSES WITH $minSeats+ SEATS\n";
                $filteredSchedule .= "Current time: " . date("d/m/Y H:i") . "\n\n";
                
                $query = "SELECT route_id, eta, location, seats_available FROM buses WHERE seats_available >= ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $minSeats);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $count = 0;
                while ($row = $result->fetch_assoc()) {
                    $filteredSchedule .= "Bus: " . $row['route_id'] . "\n";
                    $filteredSchedule .= "- ETA: " . $row['eta'] . "\n";
                    $filteredSchedule .= "- Available seats: " . $row['seats_available'] . "\n\n";
                    $count++;
                }
                
                if ($count > 0) {
                    $filteredSchedule .= "Found $count buses with $minSeats+ seats available.";
                    sendSMSMessage($phoneNumber, $filteredSchedule);
                    $response = "END Filtered schedule sent via SMS. Found $count buses with $minSeats+ seats.";
                } else {
                    $response = "END No buses found with $minSeats or more seats available.";
                }
            }
        } else if ($level[1] == "3") {
            // Show buses arriving soon
            $currentTime = date("H:i");
            $soonSchedule = "BUSES ARRIVING SOON\n";
            $soonSchedule .= "Current time: " . date("d/m/Y H:i") . "\n\n";
            
            $query = "SELECT route_id, eta, location, seats_available FROM buses ORDER BY eta ASC LIMIT 3";
            $result = $conn->query($query);
            
            while ($row = $result->fetch_assoc()) {
                $soonSchedule .= "Bus: " . $row['route_id'] . "\n";
                $soonSchedule .= "- ETA: " . $row['eta'] . "\n";
                $soonSchedule .= "- Location: " . $row['location'] . "\n";
                $soonSchedule .= "- Available seats: " . $row['seats_available'] . "\n\n";
            }
            
            $soonSchedule .= "These are the next buses to arrive. Book now!";
            sendSMSMessage($phoneNumber, $soonSchedule);
            $response = "END Information about buses arriving soon has been sent via SMS.";
        }
    } else {
        // Add more options for the schedule menu
        $response .= "\n1. Send complete schedule via SMS";
        $response .= "\n2. Filter by available seats";
        $response .= "\n3. Show buses arriving soon";
    }
} 
// Option 4: Book a Seat (for registered users)
else if ($userRegistered && $level[0] == "4") {
    if (!isset($level[1])) {
        $response = "CON Andika numero y'imodoka (urugero: RAB 456C):\n"; // Enter Bus Plate Number
        $response .= "0. Subira inyuma"; // Back to Main Menu
    } else if ($level[1] == "0") {
        // Back to main menu
        $response = getMainMenu($userRegistered, $userName);
    } else if (!isset($level[2])) {
        $busId = strtoupper(str_replace(' ', ' ', $level[1]));
        $stmt = $conn->prepare("SELECT id FROM buses WHERE route_id = ?");
        $stmt->bind_param("s", $busId);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $response = "CON Andika amazina yawe yose:\n"; // Enter your full name
            $response .= "0. Subira inyuma"; // Back to Main Menu
        } else {
            $response = "END Iyi modoka ntibashije kuyibona. Gerageza indi."; // Bus not found
        }
    } else if ($level[2] == "0") {
        // Back to main menu
        $response = getMainMenu($userRegistered, $userName);
    } else {
        $busId = strtoupper(str_replace(' ', ' ', $level[1])); // Preserve space in plate number
        $passengerName = trim($level[2]);

        // Get bus details for the SMS
        $busDetails = [];
        $stmtBus = $conn->prepare("SELECT location, eta, seats_available FROM buses WHERE route_id = ?");
        $stmtBus->bind_param("s", $busId);
        $stmtBus->execute();
        $resultBus = $stmtBus->get_result();
        if ($resultBus->num_rows > 0) {
            $busDetails = $resultBus->fetch_assoc();
        }
        $stmtBus->close();

        $stmt = $conn->prepare("INSERT INTO bookings (phone_number, bus_id, passenger_name) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $phoneNumber, $busId, $passengerName);
        if ($stmt->execute()) {
            $conn->query("UPDATE buses SET seats_available = seats_available - 1 WHERE route_id = '$busId'");
            
            // Create a detailed booking confirmation SMS
            $bookingTime = date("d/m/Y H:i");
            $msg = "BOOKING CONFIRMATION\n";
            $msg .= "Hi $passengerName,\n";
            $msg .= "Your seat on bus $busId is confirmed.\n";
            
            if (!empty($busDetails)) {
                $msg .= "Current location: " . $busDetails['location'] . "\n";
                $msg .= "ETA: " . $busDetails['eta'] . "\n";
                $msg .= "Remaining seats: " . ($busDetails['seats_available'] - 1) . "\n";
            }
            
            $msg .= "Booking time: $bookingTime\n";
            $msg .= "Thank you for choosing SmartBus!";
            
            // Send the SMS
            sendSMSMessage($phoneNumber, $msg);
            
            // Send reminder SMS
            $reminderMsg = "REMINDER: Your bus $busId is scheduled to arrive at " . 
                          (!empty($busDetails) ? $busDetails['eta'] : "the scheduled time") . 
                          ". Please be at the pickup location on time. - SmartBus";
            sendSMSMessage($phoneNumber, $reminderMsg);
            
            $response = "END Booking confirmed for $passengerName on bus $busId. SMS sent with details.";
        } else {
            $response = "END Booking failed. Try again.";
        }
    }
} 
// Option 5: Cancel Booking (for registered users)
else if ($userRegistered && $level[0] == "5") {
    if (!isset($level[1])) {
        $response = "CON Enter Bus Plate Number to cancel booking:\n";
        $response .= "0. Back to Main Menu";
    } else if ($level[1] == "0") {
        // Back to main menu
        $response  = "CON Welcome to SmartBus!\n";
        $response .= "1. Track a Bus\n";
        $response .= "2. Check Seat Availability\n";
        $response .= "3. Bus Schedule\n";
        $response .= "4. Book a Seat\n";
        $response .= "5. Cancel Booking\n";
        $response .= "6. View SMS History\n";
        $response .= "7. Exit";
    } else {
        $busId = strtoupper(str_replace(' ', ' ', $level[1])); // Preserve space in plate number
        
        // Get passenger name before deleting the booking
        $passengerName = "";
        $stmtName = $conn->prepare("SELECT passenger_name FROM bookings WHERE phone_number = ? AND bus_id = ?");
        $stmtName->bind_param("ss", $phoneNumber, $busId);
        $stmtName->execute();
        $resultName = $stmtName->get_result();
        if ($resultName->num_rows > 0) {
            $row = $resultName->fetch_assoc();
            $passengerName = $row['passenger_name'];
        }
        $stmtName->close();
        
        // Delete the booking
        $stmt = $conn->prepare("DELETE FROM bookings WHERE phone_number = ? AND bus_id = ?");
        $stmt->bind_param("ss", $phoneNumber, $busId);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            // Update available seats
            $conn->query("UPDATE buses SET seats_available = seats_available + 1 WHERE route_id = '$busId'");
            
            // Get bus details for the SMS
            $busDetails = [];
            $stmtBus = $conn->prepare("SELECT location, eta, seats_available FROM buses WHERE route_id = ?");
            $stmtBus->bind_param("s", $busId);
            $stmtBus->execute();
            $resultBus = $stmtBus->get_result();
            if ($resultBus->num_rows > 0) {
                $busDetails = $resultBus->fetch_assoc();
            }
            $stmtBus->close();
            
            // Create a detailed cancellation SMS
            $cancellationTime = date("d/m/Y H:i");
            $msg = "BOOKING CANCELLATION\n";
            if (!empty($passengerName)) {
                $msg .= "Hi $passengerName,\n";
            }
            $msg .= "Your booking on bus $busId has been cancelled.\n";
            $msg .= "Cancellation time: $cancellationTime\n";
            
            if (!empty($busDetails)) {
                $msg .= "Current available seats: " . ($busDetails['seats_available'] + 1) . "\n";
            }
            
            $msg .= "Thank you for using SmartBus.";
            
            // Send the SMS
            sendSMSMessage($phoneNumber, $msg);
            
            // Send refund confirmation
            $refundMsg = "Your refund for the cancelled booking on bus $busId has been processed. " .
                         "Please allow 3-5 business days for the amount to reflect in your account. - SmartBus";
            sendSMSMessage($phoneNumber, $refundMsg);
            
            $response = "END Booking for $busId cancelled. Cancellation SMS sent.";
        } else {
            $response = "END No booking found to cancel.";
        }
    }
} 
// Option 6: View SMS History (for registered users)
else if ($userRegistered && $level[0] == "6") {
    // Get SMS history with both sent and received messages
    $stmt = $conn->prepare("SELECT message, sent_at, message_type FROM sms_history 
                           WHERE phone_number = ? 
                           ORDER BY sent_at DESC LIMIT 5");
    $stmt->bind_param("s", $phoneNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $response = "CON Recent Messages:\n";
        $count = 1;
        
        while ($row = $result->fetch_assoc()) {
            $date = date("d/m H:i", strtotime($row['sent_at']));
            $message = substr($row['message'], 0, 30) . (strlen($row['message']) > 30 ? "..." : "");
            $type = $row['message_type'] == 'received' ? "From" : "To";
            $response .= "$count. [$date] $type: $message\n";
            $count++;
        }
        
        $response .= "\n0. Back to Main Menu";
    } else {
        $response = "CON No messages found.\n";
        $response .= "0. Back to Main Menu";
    }
}
// Option 7: Exit (for registered users)
else if ($userRegistered && $level[0] == "7") {
    $response = "END Thank you for using SmartBus.";
} 
// Invalid option
else {
    if ($userRegistered) {
        $response = "CON Wahisemo nabi. Ongera ugerageze.\n"; // Invalid option
        $response .= "1. Kureba aho imodoka igeze\n"; // Track a Bus
        $response .= "2. Kureba imyanya ihari\n"; // Check Seat Availability
        $response .= "3. Gahunda y'Ingendo\n"; // Bus Schedule
        $response .= "4. Gufata Itike\n"; // Book a Seat
        $response .= "5. Guhagarika Itike\n"; // Cancel Booking
        $response .= "6. Kureba Ubutumwa\n"; // View SMS History
        $response .= "7. Gusohoka"; // Exit
    } else {
        $response = "CON Wahisemo nabi. Ongera ugerageze.\n"; // Invalid option
        $response .= "1. Iyandikishe\n"; // Register Now
        $response .= "2. Gusohoka"; // Exit
    }
}

// Send response
header('Content-type: text/plain');
echo $response;

// Send SMS if needed
if (strpos($response, 'END') === 0) {
    try {
        // Log USSD session details
        error_log("USSD Session Details - SessionID: $sessionId, Phone: $phoneNumber");
        
        // Create meaningful message based on the response
        $message = "SmartBus: ";
        if (strpos($response, "Iyi modoka ntibashije") !== false) {
            $message .= "Iyi modoka ntibashije kuyibona. Gerageza indi numero y'imodoka."; // Bus not found
        } else if (strpos($response, "confirmed") !== false) {
            $message .= "Itike yawe yemejwe. Murakoze guhitamo SmartBus!"; // Booking confirmed
        } else if (strpos($response, "cancelled") !== false) {
            $message .= "Itike yawe yahagaritswe neza."; // Booking cancelled
        } else {
            $message .= "Murakoze gukoresha SmartBus! Kanda *123# igihe ushaka ubufasha."; // Thank you
        }
        
        // Send the SMS using our helper function
        $responseSms = sendSMSMessage($phoneNumber, $message);
        
        if ($responseSms['status'] === 'success') {
            echo "\nYou will receive a confirmation SMS shortly.";
            error_log("Failed to send SMS. Please try again later.");
        } else {
            error_log("Failed to send SMS: " . ($responseSms['message'] ?? 'Unknown error'));
            echo "\n SMS sent successfully to $phoneNumber";
        }
    } catch (Exception $e) {
        error_log("SMS Error: " . $e->getMessage());
        echo "\nFailed to send SMS. Please try again later.";
    }
}

$conn->close();
