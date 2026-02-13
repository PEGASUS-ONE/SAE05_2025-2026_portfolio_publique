package Master_worker;

import java.io.*;
import java.net.*;
import java.util.ArrayList;
import java.util.Arrays;

/** Master is a client. It makes requests to numWorkers.
 *
 */
public class MasterSocket {
    static int maxServer = 32;
    static final String[] tab_port = {"25545","25546","25547","25548","25549","25550","25551","25552"};
    static final String[] ip = {"c"};
    static final ArrayList<ArrayList<String>> tab_connexions = new ArrayList<>();
    static String[] tab_total_workers = new String[maxServer];
    static BufferedReader[] reader = new BufferedReader[maxServer];
    static PrintWriter[] writer = new PrintWriter[maxServer];
    static Socket[] sockets = new Socket[maxServer];
    static int numWorkersTotal;

    public static void main(String[] args) throws Exception {

        // MC parameters
        int totalCount = 100000; // total number of throws on a Worker
        if (args.length >= 1) {
            try {
                totalCount = Integer.parseInt(args[0]);
            } catch (NumberFormatException e) {
                System.out.println("Invalid totalCount argument, using default: 100000");
            }
}

        int total = 0; // total number of throws inside quarter of disk
        double pi;

        int numWorkers = maxServer;
        BufferedReader bufferRead = new BufferedReader(new InputStreamReader(System.in));
        String s; // for bufferRead

        ArrayList<String> c = new ArrayList<>();
        for (String p : ip) {
            for (String i : tab_port) {
                c.addAll(Arrays.asList("", ""));
                tab_connexions.add(c);
            }
        }

        System.out.println("#########################################");
        System.out.println("# Computation of PI by MC method        #");
        System.out.println("#########################################");

        int socket_stock_worker = 0;

        for (int ip_solo = 0; ip_solo<ip.length; ip_solo++){
            System.out.println("\n How many workers for computing PI (< maxServer) on IP " + ip[ip_solo] + " : ");
            try{
                s = bufferRead.readLine();
                numWorkers = Integer.parseInt(s);
                System.out.println(numWorkers);
            }
            catch(IOException ioE){
                ioE.printStackTrace();
            }

//            for (int i = 0; i < numWorkers; i++) {
//                System.out.println("Enter worker" + i + " ip : ");
//                try {
//                    s = bufferRead.readLine();
//
//                    System.out.println("You select " + s);
//                } catch (IOException ioE) {
//                    ioE.printStackTrace();
//                }
//            }

            //create worker's socket
            for (int i = 0; i < numWorkers; i++) {
                try {
                    sockets[i + socket_stock_worker] = new Socket(ip[ip_solo], Integer.parseInt(tab_port[i]));
                    System.out.println("SOCKET = " + sockets[i + socket_stock_worker]);
                } catch (Exception e) {
                    e.printStackTrace();
                }
                reader[i + socket_stock_worker] = new BufferedReader(new InputStreamReader(sockets[i + socket_stock_worker].getInputStream()));
                writer[i + socket_stock_worker] = new PrintWriter(new BufferedWriter(new OutputStreamWriter(sockets[i + socket_stock_worker].getOutputStream())), true);
            }
            socket_stock_worker += numWorkers;
        }
        setnumWorkersTotal(socket_stock_worker);
        String message_to_send;
        message_to_send = String.valueOf(totalCount);

        String message_repeat = "y";

        long stopTime, startTime;

        while (message_repeat.equals("y")){

            startTime = System.nanoTime();
            // initialize workers
            for(int i = 0 ; i < socket_stock_worker ; i++) {
                writer[i].println(message_to_send);          // send a message to each worker
            }

            //listen to workers's message
            for(int i = 0 ; i < socket_stock_worker ; i++) {
                tab_total_workers[i] = reader[i].readLine();      // read message from server
                System.out.println("Client sent: " + tab_total_workers[i]);
            }
            total = 0;
            ArrayList<Double> pi_list = new ArrayList<>();
            // compute PI with the result of each workers
            for(int i = 0 ; i < socket_stock_worker ; i++) {
                total += Integer.parseInt(tab_total_workers[i]);
//                pi_list.add(Double.parseDouble(tab_total_workers[i]));
            }

//            pi_list.sort(null);
//            pi = pi_list.get(pi_list.size() / 2);

            pi = 4.0 * total / totalCount / socket_stock_worker;

            stopTime = System.nanoTime();
            //write_in_csv(pi, stopTime - startTime, "distributedMC_step1/Data/pi_MW_results.csv");

            System.out.println("\nPi : " + pi );
            System.out.println("Error: " + (Math.abs((pi - Math.PI)) / Math.PI) +"\n");

            System.out.println("Ntot: " + totalCount*socket_stock_worker);
            System.out.println("Available processors: " + socket_stock_worker);
            System.out.println("Time Duration (ms): " + (stopTime - startTime) + "\n");

            System.out.println( (Math.abs((pi - Math.PI)) / Math.PI) +" "+ totalCount*socket_stock_worker +" "+ socket_stock_worker +" "+ (stopTime - startTime));

            System.out.println("\n Repeat computation (y/N): ");
            try{
                message_repeat = bufferRead.readLine();
                System.out.println(message_repeat);
            }
            catch(IOException ioE){
                ioE.printStackTrace();
            }
        }

        for(int i = 0 ; i < socket_stock_worker ; i++) {
            System.out.println("END");     // Send ending message
            writer[i].println("END") ;
            reader[i].close();
            writer[i].close();
            sockets[i].close();
        }

    }

    public static void setnumWorkersTotal(int totalWorkers) {
        MasterSocket.numWorkersTotal = totalWorkers;
    }
    public static int getnumWorkersTotal() {
        return MasterSocket.numWorkersTotal;
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


            // Pour calculer la scalabilité 220 de valeur initial pour G21
            writer.append(String.valueOf(220/(duration/1000000))).append(",");
            writer.append(String.valueOf(MasterSocket.getnumWorkersTotal())).append("\n");


//            writer.append(String.valueOf((Math.abs((value - Math.PI)) / Math.PI))).append(",");
//            writer.append(String.valueOf(Pi.totalCount*Pi.numWorkers)).append("\n");

        } catch (IOException e) {
            e.printStackTrace();
        }
    }

}