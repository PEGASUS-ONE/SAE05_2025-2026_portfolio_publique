package Master_worker;

import Master_worker.Master;

import java.io.FileWriter;
import java.io.IOException;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.Random;
import java.util.concurrent.Callable;
import java.util.concurrent.ExecutionException;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.Future;

/**
 * Approximates PI using the Monte Carlo method.  Demonstrates
 * use of Callables, Futures, and thread pools.
 */
public class Pi
{
    static int totalCount = 100000;
    static int numWorkers = 8;
    public static void main(String[] args) throws Exception
    {
        double pi=0;
        // 10 workers, 50000 iterations each
        for(int i=0;i<10;i++) {
            pi = new Master().doRun(totalCount, numWorkers); // Scalabilité forte divisé /numWorkers
        }
        System.out.println("Total number of masters: " + pi);
    }
}

