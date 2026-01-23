#!/bin/bash
# Enable WordPress debug logging on production server

PROD_PATH="${PROD_PATH}"
WP_CONFIG="$PROD_PATH/wp-config.php"

echo "Enabling WordPress debug logging..."

# Check if debug logging is already enabled
if grep -q "WP_DEBUG.*true" "$WP_CONFIG"; then
    echo "✅ Debug logging already enabled"
else
    echo "📝 Adding debug logging configuration..."
    # Add debug lines before "That's all, stop editing!" line
    sed -i "/^\/\* That's all, stop editing/i \\
/* WordPress debugging mode */" "$WP_CONFIG"
    
    sed -i "/^\/\* That's all, stop editing/i define( 'WP_DEBUG', true );" "$WP_CONFIG"
    sed -i "/^\/\* That's all, stop editing/i define( 'WP_DEBUG_LOG', true );" "$WP_CONFIG"
    sed -i "/^\/\* That's all, stop editing/i define( 'WP_DEBUG_DISPLAY', false );" "$WP_CONFIG"
    sed -i "/^\/\* That's all, stop editing/i @ini_set( 'display_errors', 0 );" "$WP_CONFIG"
    
    echo "✅ Debug logging enabled"
fi

# Check log file
echo ""
echo "Checking debug log file..."
if [ -f "$PROD_PATH/wp-content/debug.log" ]; then
    echo "✅ Log file exists:"
    tail -10 "$PROD_PATH/wp-content/debug.log"
else
    echo "📝 Log file will be created on first error"
fi

echo ""
echo "✅ Done! Logs will be written to: $PROD_PATH/wp-content/debug.log"
