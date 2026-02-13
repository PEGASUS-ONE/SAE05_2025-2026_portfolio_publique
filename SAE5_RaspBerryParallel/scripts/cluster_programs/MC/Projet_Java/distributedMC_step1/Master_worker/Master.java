package Master_worker;

import java.io.FileWriter;
import java.io.IOException;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.concurrent.*; /**
 * Creates workers to run the Monte Carlo simulation
 * and aggregates the results.
 */
public class Master {
    public static int doRun(int totalCount, int numWorkers) throws InterruptedException, ExecutionException {
        ArrayList<Double> piValues = new ArrayList<>();
        ArrayList<Double> durations = new ArrayList<>();
        int total = 0;
        double pi = 0;
        double startTime = 0;
        double stopTime = 0;


        for (int j = 0; j < 50; j++) {
            total = 0;
            startTime = System.nanoTime();

            // Create a collection of tasks
            List<Callable<Long>> tasks = new ArrayList<Callable<Long>>();
            for (int i = 0; i < numWorkers; ++i)
            {
                tasks.add(new Worker(totalCount));
            }

            // Run them and receive a collection of Futures
            ExecutorService exec = Executors.newFixedThreadPool(numWorkers);
            List<Future<Long>> results = exec.invokeAll(tasks);

            // Assemble the results.
            for (Future<Long> f : results)
            {
                // Call to get() is an implicit barrier.  This will block
                // until result from corresponding worker is ready.
                total += f.get();
            }
            pi = 4.0 * total / totalCount / numWorkers;

            stopTime = System.nanoTime();


            piValues.add(pi);
            double duration = stopTime - startTime;
            durations.add(duration);

            exec.shutdown();
        }
        double medianPi = calculateMedian(piValues);
        double medianDuration = calculateMedian(durations);

        //write_in_csv(medianPi, medianDuration, "data/pi_MW_results.csv");
        System.out.println("CSV: data/pi_MW_results.csv");


        System.out.println("\nPi : " + pi );
        System.out.println("Error: " + (Math.abs((pi - Math.PI)) / Math.PI) +"\n");

        System.out.println("Ntot: " + totalCount*numWorkers);
        System.out.println("Available processors: " + numWorkers);
        System.out.println("Time Duration (ms): " + (stopTime - startTime)/1000000 + "\n");

        System.out.println( (Math.abs((pi - Math.PI)) / Math.PI) +" "+ totalCount*numWorkers +" "+ numWorkers +" "+ (stopTime - startTime));
        return total;
    }


    public static void write_in_csv(double value, double duration, String filename) {
        try (FileWriter writer = new FileWriter(filename, true)) {

//            writer.append("\nPi :");
//            writer.append(String.valueOf(value)).append(",");
//
//            writer.append("Difference to exact value of pi:");
//            writer.append(String.valueOf(value - Math.PI)).append(",");
//
//            writer.append("Error:");
//            writer.append(String.valueOf((Math.abs((value - Math.PI)) / Math.PI))).append(",");
//
//            writer.append("Ntot:");
//            writer.append(String.valueOf(Pi.totalCount*Pi.numWorkers)).append(",");
//
//            writer.append("Available Workers:");
//            writer.append(String.valueOf(Pi.numWorkers)).append(",");
//
//            writer.append("Time Duration (ms):");
//            writer.append(String.valueOf(duration/1000000));


            // Pour calculer la scalabilité 3.65 de valeur initial pour G21
            writer.append(String.valueOf(3.65/(duration/1000000))).append(",");
            writer.append(String.valueOf(Pi.numWorkers)).append("\n");


//            writer.append(String.valueOf((Math.abs((value - Math.PI)) / Math.PI))).append(",");
//            writer.append(String.valueOf(Pi.totalCount*Pi.numWorkers)).append("\n");

        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    public static double calculateMedian(ArrayList<Double> list) {
        Collections.sort(list);
        int middle = list.size() / 2;
        if (list.size() % 2 == 0) {
            return (list.get(middle - 1) + list.get(middle)) / 2.0;
        } else {
            return list.get(middle);
        }
    }
}
