// ============================================================================
//
// %GENERATED_LICENSE%
//
// ============================================================================
package routines;

public class Mathematical {

    /**
     * Returns the absolute (positive) numeric value of an expression.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} double(10)
     * 
     * {example} ABS(-10) # 10
     */
    public static double ABS(double a) {
        return Math.abs(a);
    }

    /**
     * ACOS( ) Calculates the trigonometric arc-cosine of an expression.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} double(0.15)
     * 
     * {example} ACOS(0.15)
     * 
     */
    public static double ACOS(double a) {
        return Math.acos(a);
    }

    /**
     * ASIN( ) Calculates the trigonometric arc-sine of an expression.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} double(0.15)
     * 
     * {example} ASIN(0.15)
     * 
     */
    public static double ASIN(double a) {
        return Math.asin(a);
    }

    /**
     * ATAN( ) Calculates the trigonometric arctangent of an expression.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} double(3.14)
     * 
     * {example} ATAN(3.14)
     * 
     */
    public static double ATAN(double a) {
        return Math.atan(a);
    }

    /**
     * BITAND( ) Performs a bitwise AND of two integers.
     * 
     * {talendTypes} int | Integer
     * 
     * {Category} Mathematical
     * 
     * {param} int(1) a :integer
     * 
     * {param} int(2) b :integer
     * 
     * {example} BITAND(1,1)
     * 
     */
    public static int BITAND(int a, int b) {
        return a & b;
    }

    /**
     * BITNOT( ) Performs a bitwise NOT of a integers.
     * 
     * {talendTypes} int | Integer
     * 
     * {Category} Mathematical
     * 
     * {param} int(10)
     * 
     * {example} BITNOT(10)
     */
    public static int BITNOT(int a) {
        return ~a;
    }

    /**
     * BITOR( ) Performs a bitwise OR of two integers.
     * 
     * {talendTypes} int | Integer
     * 
     * {Category} Mathematical
     * 
     * {param} int(10) a: integer
     * 
     * {param} int(10) b: integer
     * 
     * {example} BITOR(10,10)
     * 
     * 
     */
    public static int BITOR(int a, int b) {
        return a | b;
    }

    // BITRESET( ) Resets one bit of an integer.

    // BITSET( ) Sets one bit of an integer.
    // BITTEST( ) Tests one bit of an integer.

    /**
     * BITXOR( ) Performs a bitwise XOR of two integers.
     * 
     * {talendTypes} int | Integer
     * 
     * {Category} Mathematical
     * 
     * {param} int(10) a: integer
     * 
     * {param} int(10) b: integer
     * 
     * {example} BITXOR(10,10)
     */
    public static int BITXOR(int a, int b) {
        return a ^ b;
    }

    /**
     * COS( ) Calculates the trigonometric cosine of an angle.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} double(3.14)
     * 
     * {example} COS(3.14)
     * 
     */
    public static double COS(double a) {
        return Math.cos(a);
    }

    /**
     * COSH( ) Calculates the hyperbolic cosine of an expression.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} double(3.14)
     * 
     * {example} COSH(3.14)
     * 
     * 
     */
    public static double COSH(double a) {
        return Math.cosh(a);
    }

    /**
     * DIV( ) Outputs the whole part of the real division of two real numbers.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} double(3.14) a: real number
     * 
     * {param} double(3.14) b: real number
     * 
     * {example} DIV(3.14,3.14)
     * 
     */
    public static int DIV(double a, double b) {
        return (int) (a / b);
    }

    /**
     * EXP( ) Calculates the result of base 'e' raised to the power designated by the value of the expression.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} double(3.14)
     * 
     * {example} EXP(3.14)
     */
    public static double EXP(double a) {
        return Math.exp(a);
    }

    /**
     * INT( ) Calculates the integer numeric value of an expression.
     * 
     * {talendTypes} int | Integer
     * 
     * {Category} Mathematical
     * 
     * {param} string("100")
     * 
     * {example} INT(\"100\")
     */
    public static int INT(String e) {
        return Integer.valueOf(e);
    }

    // FADD( ) Performs floating-point addition on two numeric values. This function is provided for compatibility with
    // existing software.
    // FDIV( ) Performs floating-point division on two numeric values.
    //

    /**
     * FFIX( ) Converts a floating-point number to a string with a fixed precision. FFIX is provided for compatibility
     * with existing software.
     * 
     * {talendTypes} String
     * 
     * {Category} Mathematical
     * 
     * {param} double(3.1415926) d: real number
     * 
     * {param} int(2) precision: precision
     * 
     * {example}FFIX(3.1415926,2)
     */
    public static String FFIX(double d, int precision) {
        double p = Math.pow(10, precision);
        d = d * p;
        d = Math.round(d) / p;
        return Double.toString(d);
    }

    /**
     * FFLT( ) Rounds a number to a string with a precision of 14.
     * 
     * {talendTypes} String
     * 
     * {Category} Mathematical
     * 
     * {param} double(3.14)
     * 
     * {example} FFLT(3.14)
     * 
     */
    public static String FFLT(double d) {
        return Mathematical.FFIX(d, 14);
    }

    // FMUL( ) Performs floating-point multiplication on two numeric values. This function is provided for compatibility
    // with existing software.
    // FSUB( ) Performs floating-point subtraction on two numeric values.

    /**
     * LN( ) Calculates the natural logarithm of an expression in base 'e'.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} double(3.14)
     * 
     * {example} LN(3.14)
     * 
     */
    public static double LN(double a) {
        return Math.log(a) / Math.E;
    }

    /**
     * MOD( ) Calculates the modulo (the remainder) of two expressions.
     * 
     * {talendTypes} String
     * 
     * {Category} Mathematical
     * 
     * {param} double(3) a: double
     * 
     * {param} double(2) b: double
     * 
     * {example} MOD(3,2)
     * 
     */
    public static double MOD(double a, double b) {
        return a % b;
    }

    public static void main(String[] args) {
        Mathematical.MOD(3, 2);
    }

    /**
     * NEG( ) Returns the arithmetic additive inverse of the value of the argument.
     * 
     * 
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} double(3.14)
     * 
     * {example} NEG(3.14)
     */
    public static double NEG(double a) {
        return -1 * a;
    }

    /**
     * NUM( ) Returns true (1) if the argument is a numeric data type; otherwise, returns false (0).
     * 
     * {talendTypes} int | Integer
     * 
     * {Category} Mathematical
     * 
     * {param} string("1")
     * 
     * {example} NUM(\"1\")
     * 
     */
    public static int NUM(String e) {
        if (e.matches("\\d+")) { //$NON-NLS-1$
            return 1;
        }
        return 0;
    }

    // PWR( ) Calculates the value of an expression when raised to a specified
    // power.

    /**
     * REAL( ) Converts a numeric expression into a real number without loss of accuracy.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} string("3.14")
     * 
     * {example} REAL(\"3.14\")
     * 
     */
    public static double REAL(String e) {
        return Double.valueOf(e);
    }

    // REM( ) Calculates the value of the remainder after integer division is
    // performed.
    // 

    /**
     * RND( ) Generates a random number between zero and a specified number minus one.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} double(3.14)
     * 
     * {example} RND(3.14)
     * 
     */
    public static double RND(double a) {
        return Math.random() * a;
    }

    /**
     * SADD( ) Adds two string numbers and returns the result as a string number.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} string("10") a: string number
     * 
     * {param} string("10") b: string number
     * 
     * {example} SADD(\"10\",\"10\")
     * 
     */
    public static double SADD(String a, String b) {
        return Double.valueOf(a) + Double.valueOf(b);
    }

    /**
     * SCMP( ) Compares two string numbers.
     * 
     * {talendTypes} int | Integer
     * 
     * {Category} Mathematical
     * 
     * {param} string("12") a: string
     * 
     * {param} string("13") b: string
     * 
     * {example} SCMP(\"12\",\"13\")
     * 
     */
    public static int SCMP(String a, String b) {
        double da = Double.valueOf(a);
        double db = Double.valueOf(b);
        if (da > db) {
            return 1;
        } else if (da == db) {
            return 0;
        } else {
            return -1;
        }
    }

    /**
     * SDIV( ) Outputs the quotient of the whole division of two integers.
     * 
     * {talendTypes} int | Integer
     * 
     * {Category} Mathematical
     * 
     * {param} int(10) a: int
     * 
     * {param} int(10) b: int
     * 
     * {example} SDIV(10,20)
     * 
     */
    public static int SDIV(int a, int b) {
        return (int) (a / b);
    }

    /**
     * SIN( ) Calculates the trigonometric sine of an angle.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} double(3.14)
     * 
     * {example} SIN(3.14)
     * 
     */
    public static double SIN(double a) {
        return Math.sin(a);
    }

    /**
     * SINH( ) Calculates the hyperbolic sine of an expression.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} double(3.14)
     * 
     * {example} SINH(3.14)
     * 
     */
    public static double SINH(double a) {
        return Math.sinh(a);
    }

    /**
     * SMUL( ) Multiplies two string numbers.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} string("3.14") a: string
     * 
     * {param} string("3.14") b: string
     * 
     * {example} SMUL(\"3.14\",\"3.14\")
     * 
     */
    public static double SMUL(String a, String b) {
        return Double.valueOf(a) * Double.valueOf(b);
    }

    /**
     * SQRT( ) Calculates the square root of a number.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} double(4)
     * 
     * {example} SQRT(4.0)
     * 
     */
    public static double SQRT(double a) {
        return Math.sqrt(a);
    }

    /**
     * SSUB( ) Subtracts one string number from another and returns the result as a string number.
     * 
     * {talendTypes} String
     * 
     * {Category} Mathematical
     * 
     * {param} string(20) a: string
     * 
     * {param} string(10) b: string
     * 
     * {example} SSUB(\"20\",\"10\")
     * 
     */
    public static String SSUB(String a, String b) {
        return Double.toString(Double.valueOf(a) - Double.valueOf(b));
    }

    /**
     * TAN( ) Calculates the trigonometric tangent of an angle.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} double(3.14) a: double
     * 
     * {example} TAN(3.14)
     * 
     * 
     */
    public static double TAN(double a) {
        return Math.tan(a);
    }

    /**
     * TANH( ) Calculates the hyperbolic tangent of an expression.
     * 
     * {talendTypes} double | Double
     * 
     * {Category} Mathematical
     * 
     * {param} double(3.14) a: double
     * 
     * {example} TANH(3.14)
     * 
     */
    public static double TANH(double a) {
        return Math.tanh(a);
    }

}
