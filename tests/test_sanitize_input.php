<?php
// Test cases for sanitizeInput function

echo "Testing sanitizeInput...\n";

assertEquals("Hello World", sanitizeInput("Hello World"), "Should return normal string as is");
assertEquals("Hello World", sanitizeInput("  Hello World  "), "Should trim whitespace");
assertEquals("Hello World", sanitizeInput("<b>Hello World</b>"), "Should strip HTML tags");
assertEquals("Hello &amp; World", sanitizeInput("Hello & World"), "Should escape special characters");
assertEquals("Hello &amp; World", sanitizeInput("  <b>Hello & World</b>  "), "Should trim, strip tags and escape special chars");
assertEquals("alert(&#039;xss&#039;)", sanitizeInput("<script>alert('xss')</script>"), "Should strip script tags and escape quotes");
assertEquals("Joe&#039;s &amp; ", sanitizeInput("Joe's & <Friends>"), "Should strip incomplete or unknown tags and escape quotes");
assertEquals("", sanitizeInput("   "), "Should return empty string for only whitespace");
assertEquals("", sanitizeInput(null), "Should handle null input");
