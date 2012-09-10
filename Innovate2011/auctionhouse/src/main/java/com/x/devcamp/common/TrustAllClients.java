package com.x.devcamp.common;

import java.io.IOException;
import java.security.GeneralSecurityException;
import java.security.KeyStore;
import java.security.KeyStoreException;
import java.security.cert.CertificateException;
import java.security.cert.X509Certificate;
import org.apache.http.conn.scheme.PlainSocketFactory;
import org.apache.http.conn.scheme.Scheme;
import org.apache.http.conn.scheme.SchemeRegistry;
import org.apache.http.conn.ssl.AllowAllHostnameVerifier;
import org.apache.http.conn.ssl.SSLSocketFactory;
import org.apache.http.conn.ssl.TrustStrategy;

/*
 * @author Muasir Khalil
 */
public class TrustAllClients {
	public static SchemeRegistry getSchemeRegistry() {
		try {
			// Change the TrustStrategy
			TrustStrategy trustStrategy = new TrustStrategy() {
				public boolean isTrusted(X509Certificate[] chain,
						String authType) throws CertificateException {
					// TODO Auto-generated method stub
					return true;
				}
			};
			// null keystore
			KeyStore trustStore = KeyStore.getInstance( KeyStore
			        .getDefaultType() );
			try {
				trustStore.load( null, null );
			}
			catch ( IOException e ) {
				// Not supposed to happen when passing null, but rethrow just
				// incase
				throw new KeyStoreException( "Cannot create empty keystore: "
				        + e.getMessage(), e );
			}

			// initialize the SSLSocketFactory based on our own KeyStore, and
			// TrustStrategy
			SSLSocketFactory sf = new SSLSocketFactory( "TLS", null, null,
			        trustStore, null, trustStrategy,
			        new AllowAllHostnameVerifier() );
			// initilize the scheme registry with default ports
			SchemeRegistry registry = new SchemeRegistry();
			registry.register( new Scheme( "http", 80, PlainSocketFactory
			        .getSocketFactory() ) );
			registry.register( new Scheme( "https", 443, sf ) );
			return registry;
		}
		catch ( GeneralSecurityException e ) {
			// Not expected, but rethrow if it happens
			throw new RuntimeException( "Could not create SchemeRegistry: "
			        + e.getMessage(), e );
		}
	}
}