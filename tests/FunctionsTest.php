<?php
// FunctionsTest.php

/**
 * Test cases for sanitizeInput
 */

// Test basic string
assertEquals('test', sanitizeInput('test'), 'Basic string should remain unchanged');

// Test whitespace trimming
assertEquals('test', sanitizeInput('  test  '), 'Leading and trailing whitespace should be trimmed');

// Test HTML tag stripping
assertEquals('test', sanitizeInput('<script>test</script>'), 'HTML tags should be stripped');
assertEquals('test', sanitizeInput('<b>test</b>'), 'HTML tags should be stripped');

// Test special character encoding
assertEquals('test &amp; &quot; &#039; &lt; &gt;', sanitizeInput('test & " \' < >'), 'Special characters should be encoded');

// Test combination
assertEquals('test &amp; alert', sanitizeInput('  <b>test</b> & <script>alert</script>  '), 'Combined sanitization failed');

// Test empty string
assertEquals('', sanitizeInput(''), 'Empty string should return empty string');
assertEquals('', sanitizeInput('   '), 'Whitespace-only string should return empty string');
